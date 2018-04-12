<?php
class Papucho {

    function __construct() {
        add_action( 'admin_menu', array($this, 'menu_options') );
        add_action( 'admin_enqueue_scripts', array($this, 'admin_assets') );
        add_action('wp_ajax_papucho_review', array($this, 'ajax_papucho_review') );
        add_action('wp_ajax_papucho_done', array($this, 'ajax_papucho_done') );
    }

    function admin_assets() {

        wp_enqueue_style( 'papucho_admin', plugin_dir_url( __FILE__ ) . 'assets/css/papucho.css', array(), time() );
        wp_enqueue_script( 'papucho_admin', plugin_dir_url( __FILE__ ) . 'assets/js/papucho.js', array(), time(), true );

    }

    function menu_options() {

        add_submenu_page(
            'tools.php',
            __( 'Importador Usuarios', 'papuchobots' ),
            __( 'Importador Usuarios', 'papuchobots' ),
            'manage_options',
            'papucho-bots',
            array($this, 'page_options')
        );
    }

    function page_options() { ?>
        <h1><?php _e( 'Importador Usuarios', 'papuchobots' ) ?></h1>
        <form id="papucho_import" action="<?php echo admin_url('admin-ajax.php'); ?>" method="post" enctype="multipart/form-data">
            <p>Seleccionar archivo a importar (.csv)<br>
                <input type="file" name="papucho_file" />
            </p>
            <input type="hidden" name="action" value="papucho_review">
            <?php wp_nonce_field( 'papucho_review', 'wpnonce' ); ?>
            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Revisar'); ?>">
                <a href="<?php echo plugin_dir_url( __FILE__ ) . 'assets/demo/demo.csv'; ?>" class="button button-button" target="blank">
                    <?php _e('Descargar Ejemplo', 'papuchobots'); ?>
                </a>
            </p>
        </form>
        <form id="papucho_review" action="<?php echo admin_url('admin-ajax.php'); ?>" method="post"></form>
        <div id="papucho_done"></div>
    <?php }

    function ajax_papucho_review(){

        if ( ! wp_verify_nonce( $_POST['wpnonce'], 'papucho_review' ) ) die ( 'Busted!');

        $csv = array();
        $lines = file($_FILES['papucho_file']['tmp_name'], FILE_IGNORE_NEW_LINES); ?>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th scope="col" class="manage-column column-author">username</th>
                    <th scope="col" class="manage-column column-author">email</th>
                    <th scope="col" class="manage-column column-author">nombres</th>
                    <th scope="col" class="manage-column column-author">apellidos</th>
                    <th scope="col" class="manage-column column-author">contraseña</th>
                    <th scope="col" class="manage-column column-author">cargo</th>
                    <th scope="col" class="manage-column column-author">sede</th>
                    <th scope="col" class="manage-column column-author">fecha_nacimiento</th>
                    <th scope="col" class="manage-column column-author">fecha_ingreso</th>
                    <th scope="col" class="manage-column column-author">telefono</th>
                    <th scope="col" class="manage-column column-author">anexo</th>
                </tr>
            </thead>
            <tbody id="the-list">
                <?php $c = 0;
                foreach ($lines as $key => $value) {
                    $row = str_getcsv($value);
                    if ($c > 0) { ?>
                        <tr>
                            <td class="author column-author"><?php echo $row[0]; ?></td>
                            <td class="author column-author"><?php echo $row[1]; ?></td>
                            <td class="author column-author"><?php echo $row[2]; ?></td>
                            <td class="author column-author"><?php echo $row[3]; ?></td>
                            <td class="author column-author"><?php echo $row[4]; ?></td>
                            <td class="author column-author"><?php echo $row[5]; ?></td>
                            <td class="author column-author"><?php echo $row[6]; ?></td>
                            <td class="author column-author"><?php echo $row[7]; ?></td>
                            <td class="author column-author"><?php echo $row[8]; ?></td>
                            <td class="author column-author"><?php echo $row[9]; ?></td>
                            <td class="author column-author"><?php echo $row[10]; ?></td>
                            <td class="author column-author"><?php echo $row[11]; ?></td>
                        </tr>
                        <input type="hidden" name="row_<?php echo ($c - 1); ?>" value="<?php echo implode('%%%', $row); ?>" />
                    <?php }
                    $c++;
                } ?>
            </tbody>
        </table>
        <input type="hidden" name="total" value="<?php echo ($c - 1); ?>" />
        <input type="hidden" name="action" value="papucho_done">
        <?php wp_nonce_field( 'papucho_done', 'wpnonce' ); ?>
        <input type="submit" name="submit" id="submit" class="button button-primary" value="Importar usuarios">

        <?php echo $output;
        exit();

    }

    function ajax_papucho_done(){

        if ( ! wp_verify_nonce( $_POST['wpnonce'], 'papucho_done' ) ) die ( 'Busted!');

        $output = '<h3>Usuarios Registrados</h3>';
        $output .= '<p>';
        for ($i=0; $i < $_POST['total']; $i++) {
            $data = explode('%%%', $_POST['row_'.$i]);
            $userdata = array(
                'user_login'  =>  $data[0],
                'user_email'  =>  $data[1],
                'first_name'  =>  $data[2],
                'last_name'   =>  $data[3],
                'user_pass'   =>  $data[4]
            );

            $user_id = wp_insert_user( $userdata ) ;
            if ( ! is_wp_error( $user_id ) ) {
                add_user_meta($user_id, 'cargo', $data[5]);
                add_user_meta($user_id, 'sede', $data[6]);
                add_user_meta($user_id, 'birthday', date('Ymd', strtotime($data[7])));
                add_user_meta($user_id, 'fecha_de_ingreso', date('Ymd', strtotime($data[8])));
                add_user_meta($user_id, 'telefono', $data[9]);
                add_user_meta($user_id, 'anexo', $data[10]);
                $output .= '<a href="'.admin_url('user-edit.php?user_id='.$user_id).'" target="blank">"'.$data[0].'" registrado correctamente</a><br>';
            } else {
                $output .= '"'.$data[0].'": '.$user_id->get_error_message();
            }
        }
        $output .= '</p>';
        echo $output;
        exit();
    }

}
?>
