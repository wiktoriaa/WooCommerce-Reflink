<?php
/*
Plugin Name: WooCommerce Reflink
Description: System polecenia strony (reflinki) zintegrowany z WooCommerce
Version: 0.1.0
*/

wp_enqueue_style('style', plugins_url( 'style.css', __FILE__ ));

/* Create database */

global $wpdb;

$charset_collate = $wpdb->get_charset_collate();
$table_name = $wpdb->prefix . "reflinks";

$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		user_id TEXT NOT NULL,
		register_id TEXT NOT NULL,
		open_box TEXT NOT NULL,
		UNIQUE KEY id (id)
	) $charset_collate;";

require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
dbDelta( $sql );

$charset_collate = $wpdb->get_charset_collate();
$table_name = $wpdb->prefix . "reflinks_winner";

$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		user_id TEXT NOT NULL,
		UNIQUE KEY id (id)
	) $charset_collate;";

require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
dbDelta( $sql );


add_action( 'init', 'create_shortcode' );
add_action('woocommerce_account_dashboard', 'create_reflink');
add_action('user_register', 'check_reflink');

function create_reflink()
{	
	global $wpdb;
	?>
	<h2>Wygraj darmową skrzynkę!</h2>
	
	<h3>Link polecający</h3>
	<p>Udostępnij ten link i jeśli przynajmniej 15 osób się zarejestruje klikając w poniższy url i otworzy przynajmniej 1 boxa, otrzymasz darmową skrzynkę!</p><a class="reflink">
	<?php
	echo add_query_arg( array(
   			'ref' => get_current_user_id(),
		), 'http://losujboxa.pl' );
	echo '</a><br>';

	$count = $wpdb->get_var("SELECT COUNT(*) FROM `wp_reflinks` WHERE user_id = " . get_current_user_id() );
	$rowcount = $wpdb->get_var("SELECT COUNT(*) FROM `wp_reflinks` WHERE user_id = " . get_current_user_id() . " AND open_box = 1");
	$is_winner = $wpdb->get_var("SELECT id FROM `wp_reflinks_winner` WHERE user_id = " . get_current_user_id());
	?>
	<p>Dotychczas przy użyciu twojego linku zarejestrowało się <?php echo $count ?> użytkowników. <?php echo $rowcount ?> z nich otworzyło boxa.</p>
	<?php
	
	if ($rowcount >= 15 && $is_winner <= 0) {
		echo '<br><p>Gratulacje! Udało ci się wygrać darmową skrzynkę!</p>';
		do_shortcode('[prize-generator]');

	}

}

function create_cookie()
{
	$val = filter_input( INPUT_GET, "ref", FILTER_SANITIZE_STRING );
	setcookie('reflink', $val);
}

function ref_code()
{
	return '<a class="reflink">' . add_query_arg( array(
   			'ref' => get_current_user_id(),
		), 'http://losujboxa.pl' ) . '</a>';

}

function create_shortcode()
{
	add_shortcode("reflink", "create_cookie");
	add_shortcode("ref_code", "ref_code");
}

function check_reflink($user_id)
{
	global $wpdb;
	$cookie_name = 'reflink';
	if (isset($_COOKIE['reflink'])) {

	  	$wpdb->insert($wpdb->prefix . "reflinks", array(
                              'user_id'  	=> intval($_COOKIE['reflink']),
							  'register_id' => $user_id,
							  'open_box' 	=> 0
                             ));
	}

}

?>
