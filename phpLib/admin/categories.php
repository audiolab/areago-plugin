<?php
    global $wpdb;
    if (isset($_POST['action'])) {
        if ($_POST['action']=='SoinuMapa') {
            /*
             * We have to save the post values for each category.
             */
             $categories=get_categories('hide_empty=0');
             foreach ($categories as $cat) {
                    $q = "SELECT icon FROM ";
                    $q .=$wpdb->prefix;
                    $q .="sm_Categories WHERE id= $cat->term_id";
                    $icon = $wpdb->get_var($q);
                    //print_r (array_keys($_POST));

                    $catname= preg_replace('/\s+/', '_', $cat->cat_name );
                    if ($icon!=$_POST[$catname]) {
                        // We have to save the new value.
                        // If we have one, we update, else, we insert
                        if($icon==null) {
                            //we have to insert a new line
                                $q = "INSERT INTO " . $wpdb->prefix . "sm_Categories (id, icon) VALUES (" . $cat->term_id . ",'" . $_POST[$catname] . "')";
                                $wpdb->query($q);

                         } else {
                                $q = "UPDATE " . $wpdb->prefix . "sm_Categories SET icon='" . $_POST[$catname] . "' WHERE id=" . $cat->term_id;
                                $wpdb->query($q);
                         }

                    }
             }
             
        }

    }

?>
<div class="wrap">
    <form method="post"   id="sm_option-form">
        <?php
        $pluginPath = WP_CONTENT_URL . "/plugins/" . plugin_basename(dirname(__FILE__));
        $categories=  get_categories('hide_empty=0');
        ?>
        <style type="text/css">

            div#sm_cat_left label{
                display: block;
                width: 250px;

            }
            div#sm_cat_left input{
                display: block;
                width: 250px;

            }
        </style>
        <div id="sm_categories">
            <h2>Select the icon for each category:</h2>
            <div id="sm_cat_left" style="float: left">

                <?php
                foreach ($categories as $cat) {
                    $o = '<p><label title="' . $cat->term_id . '">' . $cat->cat_name . '</label>';
                    $o .= '<input type="text" name="' . $cat->cat_name . '" id="' . $cat->cat_name . '" value="';
                    $q = "SELECT icon FROM ";
                    $q .=$wpdb->prefix;
                    $q .="sm_Categories WHERE id= $cat->term_id";
                    $icon = $wpdb->get_var($q);
                    if ( $icon == null ){
                        $o .= '"></p>';
                    } else {
                        $o .= $icon . '"></p>';
                    }

                    echo $o;
                }


                ?>

            </div>
            <div id="sm_cat_right" style="width: 400px; display: block;float: right;">
                <table class="widefat fixed" cellspacing="0">
                    <thead>
                        <tr>
                            <th>
                                Icon
                            </th>
                            <th>
                                Name
                            </th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>
                                Icon
                            </th>
                            <th>
                                Name
                            </th>
                        </tr>
                    </tfoot>
                    <tbody>
                        <?php

                        foreach ($categories as $cat) {
                            $q = "SELECT icon FROM ";
                            $q .=$wpdb->prefix;
                            $q .="sm_Categories WHERE id= $cat->term_id";
                            $icon = $wpdb->get_var($q);
                            if ( $icon == null ){

                                $row = '<tr><th></th>';


                            } else {
                                $row='<tr><th><img src= "' . $pluginPath . '/icons/' . $icon . '"></th>';
                                //$row = "<tr><th>$icon</th>";
                            }
                            $row .= '<th>'.$cat->cat_name.'</th></tr>';
                            echo $row;
                        }
                        ?>

                    </tbody>
                </table>
            </div>

        </div>
        <input type="hidden" name="action" value="SoinuMapa" />
        <p class="submit" style="clear:both" >
            <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" style="clear:both" />
        </p>
    </form>

</div>
