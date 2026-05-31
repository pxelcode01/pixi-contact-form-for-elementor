<?php
/**
 * Pixi Form Entries
 *
 * - Creates DB table on activation
 * - Handles AJAX submission with:
 *     • Multiple entries from DIFFERENT emails = allowed
 *     • Same email submitting again = blocked (configurable per widget)
 * - Admin dashboard: entries table, search, bulk delete, mark-read, badges
 */

if ( ! defined( 'ABSPATH' ) ) exit;


// =========================================================
//  1. CREATE DB TABLE ON PLUGIN ACTIVATE
// =========================================================
register_activation_hook(
    dirname( __DIR__ ) . '/pixi-contact-form-for-elementor.php',
    'pixi_create_entries_table'
);

function pixi_create_entries_table() {
    global $wpdb;
    $table   = $wpdb->prefix . 'pixi_form_entries';
    $charset = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS {$table} (
        id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        name         VARCHAR(150)    NOT NULL DEFAULT '',
        email        VARCHAR(150)    NOT NULL DEFAULT '',
        phone        VARCHAR(80)     NOT NULL DEFAULT '',
        selected_val TEXT,
        checkboxes   TEXT,
        message      TEXT,
        ip_address   VARCHAR(60)     NOT NULL DEFAULT '',
        submitted_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
        is_read      TINYINT(1)      NOT NULL DEFAULT 0,
        PRIMARY KEY (id),
        KEY idx_email (email),
        KEY idx_submitted_at (submitted_at)
    ) {$charset};";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );
}


// =========================================================
//  2. AJAX HANDLER
//     - Allows multiple entries per email if widget says so
//     - Blocks same email if widget says no duplicates
// =========================================================
add_action( 'wp_ajax_pixi_submit_form',        'pixi_handle_form_submit' );
add_action( 'wp_ajax_nopriv_pixi_submit_form', 'pixi_handle_form_submit' );

function pixi_handle_form_submit() {

    // ── Security ─────────────────────────────────────────
    if ( empty( $_POST['pixi_nonce'] ) ||
         ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pixi_nonce'] ) ), 'pixi_form_nonce' ) ) {
        wp_send_json_error( [ 'message' => 'Security verification failed. Please refresh and try again.' ] );
    }

    // ── Validate email ───────────────────────────────────
    $email = sanitize_email( wp_unslash( $_POST['pixi_email'] ?? '' ) );
    if ( empty( $email ) || ! is_email( $email ) ) {
        wp_send_json_error( [ 'message' => 'Please enter a valid email address.' ] );
    }

    // ── Duplicate email check ────────────────────────────
    // The widget passes pixi_allow_dup = '1' when duplicates are allowed.
    // Default (not sent / '0') = block duplicate emails.
    $allow_dup = ( isset( $_POST['pixi_allow_dup'] ) && $_POST['pixi_allow_dup'] === '1' );

    if ( ! $allow_dup ) {
        global $wpdb;
        $exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}pixi_form_entries WHERE email = %s",
                $email
            )
        );
        if ( $exists > 0 ) {
            $msg = sanitize_text_field( wp_unslash( $_POST['pixi_dup_msg'] ?? 'This email has already been submitted.' ) );
            wp_send_json_error( [ 'message' => $msg ] );
        }
    }

    // ── Sanitize fields ──────────────────────────────────
    $name    = sanitize_text_field( wp_unslash( $_POST['pixi_name']    ?? '' ) );
    $phone   = sanitize_text_field( wp_unslash( $_POST['pixi_phone']   ?? '' ) );
    $message = sanitize_textarea_field( wp_unslash( $_POST['pixi_message'] ?? '' ) );
    $ip      = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) );

    // ── Select value(s) ──────────────────────────────────
    $selected_val = '';
    if ( ! empty( $_POST['pixi_select'] ) ) {
        if ( is_array( $_POST['pixi_select'] ) ) {
            $selected_val = implode( ', ', array_map( 'sanitize_text_field', wp_unslash( $_POST['pixi_select'] ) ) );
        } else {
            $selected_val = sanitize_text_field( wp_unslash( $_POST['pixi_select'] ) );
        }
    }

    // ── Checkboxes ───────────────────────────────────────
    $checkboxes = '';
    if ( ! empty( $_POST['pixi_checkboxes'] ) && is_array( $_POST['pixi_checkboxes'] ) ) {
        $checkboxes = implode( ', ', array_map( 'sanitize_text_field', wp_unslash( $_POST['pixi_checkboxes'] ) ) );
    }

    // ── Insert ───────────────────────────────────────────
    global $wpdb;
    $inserted = $wpdb->insert(
        $wpdb->prefix . 'pixi_form_entries',
        [
            'name'         => $name,
            'email'        => $email,
            'phone'        => $phone,
            'selected_val' => $selected_val,
            'checkboxes'   => $checkboxes,
            'message'      => $message,
            'ip_address'   => $ip,
        ],
        [ '%s', '%s', '%s', '%s', '%s', '%s', '%s' ]
    );

    if ( $inserted === false ) {
        wp_send_json_error( [ 'message' => 'Could not save your submission. Please try again later.' ] );
    }

    wp_send_json_success( [ 'message' => 'Message sent successfully! We will be in touch soon.' ] );
}


// =========================================================
//  3. ADMIN MENU
// =========================================================
add_action( 'admin_menu', 'pixi_register_entries_menu' );

function pixi_register_entries_menu() {
    add_menu_page(
        __( 'Pixi Form Entries', 'pixi' ),
        __( 'Pixi Entries',      'pixi' ),
        'manage_options',
        'pixi-form-entries',
        'pixi_render_entries_page',
        'dashicons-email-alt2',
        25
    );
}


// =========================================================
//  4. ENTRY ACTIONS (delete single, bulk delete, mark read)
// =========================================================
add_action( 'admin_init', 'pixi_handle_entry_actions' );

function pixi_handle_entry_actions() {
    if ( ! current_user_can( 'manage_options' ) ) return;

    // ── Delete single ────────────────────────────────────
    if ( ! empty( $_GET['pixi_delete'] ) && ! empty( $_GET['_wpnonce'] ) ) {
        if ( wp_verify_nonce( sanitize_text_field( $_GET['_wpnonce'] ), 'pixi_delete_entry' ) ) {
            global $wpdb;
            $wpdb->delete(
                $wpdb->prefix . 'pixi_form_entries',
                [ 'id' => absint( $_GET['pixi_delete'] ) ],
                [ '%d' ]
            );
            wp_redirect( admin_url( 'admin.php?page=pixi-form-entries&pixi_notice=deleted_1' ) );
            exit;
        }
    }

    // ── Bulk delete ──────────────────────────────────────
    if ( ! empty( $_POST['pixi_bulk_action'] ) && $_POST['pixi_bulk_action'] === 'delete_selected'
         && ! empty( $_POST['entry_ids'] ) && is_array( $_POST['entry_ids'] )
         && ! empty( $_POST['pixi_bulk_nonce'] )
         && wp_verify_nonce( sanitize_text_field( $_POST['pixi_bulk_nonce'] ), 'pixi_bulk_action' )
    ) {
        global $wpdb;
        $count = 0;
        foreach ( array_map( 'absint', $_POST['entry_ids'] ) as $id ) {
            $wpdb->delete( $wpdb->prefix . 'pixi_form_entries', [ 'id' => $id ], [ '%d' ] );
            $count++;
        }
        wp_redirect( admin_url( 'admin.php?page=pixi-form-entries&pixi_notice=deleted_' . $count ) );
        exit;
    }

    // ── Mark as read ─────────────────────────────────────
    if ( ! empty( $_GET['pixi_mark_read'] ) && ! empty( $_GET['_wpnonce'] ) ) {
        if ( wp_verify_nonce( sanitize_text_field( $_GET['_wpnonce'] ), 'pixi_mark_read' ) ) {
            global $wpdb;
            $wpdb->update(
                $wpdb->prefix . 'pixi_form_entries',
                [ 'is_read' => 1 ],
                [ 'id'      => absint( $_GET['pixi_mark_read'] ) ],
                [ '%d' ], [ '%d' ]
            );
            wp_redirect( admin_url( 'admin.php?page=pixi-form-entries' ) );
            exit;
        }
    }
}


// =========================================================
//  5. RENDER ADMIN ENTRIES PAGE
// =========================================================
function pixi_render_entries_page() {
    global $wpdb;
    $table = $wpdb->prefix . 'pixi_form_entries';

    // Search
    $search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
    $where  = '';
    if ( $search ) {
        $like  = '%' . $wpdb->esc_like( $search ) . '%';
        $where = $wpdb->prepare(
            "WHERE (name LIKE %s OR email LIKE %s OR message LIKE %s OR selected_val LIKE %s OR checkboxes LIKE %s)",
            $like, $like, $like, $like, $like
        );
    }

    $entries      = $wpdb->get_results( "SELECT * FROM {$table} {$where} ORDER BY submitted_at DESC" );
    $total        = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
    $unread_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE is_read = 0" );

    // Unique emails stat
    $unique_emails = (int) $wpdb->get_var( "SELECT COUNT(DISTINCT email) FROM {$table}" );

    // Notices
    $notice = isset( $_GET['pixi_notice'] ) ? sanitize_text_field( $_GET['pixi_notice'] ) : '';
    $deleted_count = 0;
    if ( str_starts_with( $notice, 'deleted_' ) ) {
        $deleted_count = (int) str_replace( 'deleted_', '', $notice );
    }
    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">📬 <?php esc_html_e( 'Pixi Form Entries', 'pixi' ); ?></h1>
        <hr class="wp-header-end">

        <?php if ( $deleted_count > 0 ) : ?>
        <div class="notice notice-success is-dismissible">
            <p><?php printf( esc_html__( '%d entry/entries deleted successfully.', 'pixi' ), $deleted_count ); ?></p>
        </div>
        <?php endif; ?>

        <!-- ── Stats Bar ────────────────────────────── -->
        <div style="display:flex;gap:16px;margin:16px 0;flex-wrap:wrap;">
            <?php
            $stats = [
                [ 'val' => $total,         'label' => 'Total Entries',  'color' => '#0073aa' ],
                [ 'val' => $unread_count,  'label' => 'Unread',         'color' => '#e65100' ],
                [ 'val' => $unique_emails, 'label' => 'Unique Emails',  'color' => '#2e7d32' ],
            ];
            foreach ( $stats as $st ) : ?>
            <div style="background:#fff;border:1px solid #ddd;border-radius:8px;padding:12px 24px;min-width:130px;text-align:center;">
                <div style="font-size:26px;font-weight:700;color:<?php echo esc_attr($st['color']); ?>">
                    <?php echo esc_html( $st['val'] ); ?>
                </div>
                <div style="color:#666;font-size:12px;margin-top:2px;"><?php echo esc_html( $st['label'] ); ?></div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- ── Search ───────────────────────────────── -->
        <form method="get" style="margin-bottom:16px;">
            <input type="hidden" name="page" value="pixi-form-entries" />
            <input type="search" name="s" value="<?php echo esc_attr($search); ?>"
                   placeholder="<?php esc_attr_e('Search name, email, message, subject, checkboxes…', 'pixi'); ?>"
                   style="width:340px;" />
            <input type="submit" class="button" value="<?php esc_attr_e('Search','pixi'); ?>" />
            <?php if ( $search ) : ?>
            <a href="<?php echo esc_url(admin_url('admin.php?page=pixi-form-entries')); ?>" class="button">
                <?php esc_html_e('Clear','pixi'); ?>
            </a>
            <?php endif; ?>
        </form>

        <!-- ── Bulk form ─────────────────────────────── -->
        <form method="post">
            <?php wp_nonce_field( 'pixi_bulk_action', 'pixi_bulk_nonce' ); ?>

            <div style="display:flex;gap:8px;align-items:center;margin-bottom:12px;flex-wrap:wrap;">
                <select name="pixi_bulk_action">
                    <option value=""><?php esc_html_e('— Bulk Actions —','pixi'); ?></option>
                    <option value="delete_selected"><?php esc_html_e('Delete Selected','pixi'); ?></option>
                </select>
                <button type="submit" class="button"><?php esc_html_e('Apply','pixi'); ?></button>
                <span style="color:#888;font-size:13px;margin-left:8px;">
                    <?php printf( esc_html__('Showing %d of %d entries','pixi'), count($entries), $total ); ?>
                </span>
            </div>

            <?php if ( empty($entries) ) : ?>
                <div style="background:#fff;border:1px solid #ddd;border-radius:8px;padding:48px;text-align:center;color:#999;">
                    <?php echo $search
                        ? esc_html__('No entries match your search.','pixi')
                        : esc_html__('No form entries yet. Submissions will appear here.','pixi');
                    ?>
                </div>
            <?php else : ?>

            <table class="wp-list-table widefat fixed striped" style="border-radius:8px;overflow:hidden;">
                <thead>
                    <tr>
                        <th style="width:28px;"><input type="checkbox" id="pixi-check-all" /></th>
                        <th style="width:40px;">#</th>
                        <th><?php esc_html_e('Name','pixi'); ?></th>
                        <th><?php esc_html_e('Email','pixi'); ?></th>
                        <th><?php esc_html_e('Phone','pixi'); ?></th>
                        <th><?php esc_html_e('Subject / Select','pixi'); ?></th>
                        <th><?php esc_html_e('Checkboxes','pixi'); ?></th>
                        <th><?php esc_html_e('Message','pixi'); ?></th>
                        <th style="width:140px;"><?php esc_html_e('Date','pixi'); ?></th>
                        <th style="width:100px;"><?php esc_html_e('Actions','pixi'); ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ( $entries as $entry ) :
                    $unread_style  = $entry->is_read ? '' : 'background:#fffbf0;';
                    $unread_badge  = $entry->is_read ? '' : '<span style="background:#e65100;color:#fff;font-size:10px;padding:1px 6px;border-radius:10px;margin-left:4px;font-weight:normal;">NEW</span>';
                    $delete_url    = wp_nonce_url( admin_url('admin.php?page=pixi-form-entries&pixi_delete='  . $entry->id), 'pixi_delete_entry' );
                    $mark_read_url = wp_nonce_url( admin_url('admin.php?page=pixi-form-entries&pixi_mark_read=' . $entry->id), 'pixi_mark_read'   );
                ?>
                <tr style="<?php echo esc_attr($unread_style); ?>">
                    <td><input type="checkbox" name="entry_ids[]" value="<?php echo esc_attr($entry->id); ?>" /></td>
                    <td style="color:#888;font-size:12px;"><?php echo esc_html($entry->id); ?></td>
                    <td style="font-weight:<?php echo $entry->is_read ? 'normal' : '700'; ?>">
                        <?php echo esc_html($entry->name ?: '—'); ?>
                        <?php echo $unread_badge; ?>
                    </td>
                    <td>
                        <a href="mailto:<?php echo esc_attr($entry->email); ?>">
                            <?php echo esc_html($entry->email); ?>
                        </a>
                    </td>
                    <td><?php echo esc_html($entry->phone ?: '—'); ?></td>
                    <td>
                        <?php if ( $entry->selected_val ) :
                            foreach ( explode(', ', $entry->selected_val) as $sv ) : ?>
                            <span style="display:inline-block;background:#e3f2fd;color:#1565c0;border-radius:4px;padding:2px 7px;font-size:11px;margin:2px;">
                                <?php echo esc_html($sv); ?>
                            </span>
                        <?php endforeach; else : ?>—<?php endif; ?>
                    </td>
                    <td>
                        <?php if ( $entry->checkboxes ) :
                            foreach ( explode(', ', $entry->checkboxes) as $cb ) : ?>
                            <span style="display:inline-block;background:#e8f5e9;color:#2e7d32;border-radius:4px;padding:2px 7px;font-size:11px;margin:2px;">
                                ✓ <?php echo esc_html($cb); ?>
                            </span>
                        <?php endforeach; else : ?>—<?php endif; ?>
                    </td>
                    <td style="white-space:normal;max-width:200px;color:#555;font-size:13px;">
                        <?php echo esc_html( wp_trim_words($entry->message, 14, '…') ?: '—' ); ?>
                    </td>
                    <td style="font-size:12px;color:#666;white-space:nowrap;">
                        <?php echo esc_html(
                            date_i18n(
                                get_option('date_format') . ' ' . get_option('time_format'),
                                strtotime($entry->submitted_at)
                            )
                        ); ?>
                    </td>
                    <td style="white-space:nowrap;">
                        <?php if ( ! $entry->is_read ) : ?>
                        <a href="<?php echo esc_url($mark_read_url); ?>"
                           class="button button-small" title="<?php esc_attr_e('Mark as read','pixi'); ?>">✔</a>
                        <?php endif; ?>
                        <a href="<?php echo esc_url($delete_url); ?>"
                           class="button button-small"
                           style="color:#b32d2e;"
                           onclick="return confirm('<?php esc_attr_e('Delete this entry? This cannot be undone.','pixi'); ?>')"
                           title="<?php esc_attr_e('Delete','pixi'); ?>">🗑</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <?php endif; /* entries */ ?>
        </form>

    </div><!-- .wrap -->

    <script>
    (function(){
        var checkAll = document.getElementById('pixi-check-all');
        if ( ! checkAll ) return;
        checkAll.addEventListener('change', function(){
            document.querySelectorAll('input[name="entry_ids[]"]')
                    .forEach(function(cb){ cb.checked = checkAll.checked; });
        });
    })();
    </script>
    <?php
}