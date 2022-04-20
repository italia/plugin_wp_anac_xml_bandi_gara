<?php

function avcp_v_dataset_load()
{
    $terms = get_terms( 'annirif', array('hide_empty' => 0) );

    foreach ( $terms as $term ) {
        if( isset( $_POST['XMLGEN_'.$term->name] ) ) {
            creafilexml ($term->name);
            echo '<div class="updated"><p>';
            printf( 'Il seguente file .xml è generato: <b>' . $term->name . '</b>' );
            echo "</p></div>";
            break;
        }
    }
    if(isset($_POST['AVCP-RESET-LOG'])) {
        delete_option('anac_log');
        echo '<div class="updated"><p>';
        printf( 'Log eliminati' );
        echo "</p></div>";
    }

    echo '<div class="wrap">';
    echo '<div style="padding:20px;box-shadow: 0 1px 3px rgba(0,0,0,0.2);background-color: #fff;">
    <h1>Dashboard ANAC</h1>
    La funzione effettua un controllo puramente <b>formale</b> e non attesta la correttezza dei dati. Puoi controllare online i dataset con un <a href="https://anac.softcare.it/Validator" target="_blank">validatore esterno</a>
    </div>'; ?>

<div id="poststuff">
    <div id="post-body" class="metabox-holder columns-2">
        <div id="post-body-content">
            <div class="meta-box-sortables ui-sortable">
                <div class="postbox">
                    <h2><span style="float:right;">
                        <a href="<?php echo avcp_get_basexmlurl(); ?>" target="_blank"><?php echo avcp_get_basexmlurl(); ?></a>
                    </span>File XML generati</h2>

                    <div class="inside">
                        <?php
                        query_posts( array( 'post_type' => 'avcp', 'posts_per_page' => '-1') ); global $post;
                        $erroreanno = false;
                        $ng = 0;
                        if ( have_posts() ) : while ( have_posts() ) : the_post();
                                $ng++;
                                if(!( has_term( '', 'annirif' ) )) {
                                    $erroreanno = true;
                                }
                        endwhile; else:
                        endif;
                        if ($erroreanno == true) {
                            echo '<center><font style="background-color:red;color:white;padding:5px;font-weight:bold;">Una o più gare sono registrate senza anno di riferimento. Correggere!</font></center><br/>';
                        }
                        ?>
                        <form method="post" name="options" target="_self">
                            <table class="form-table">
                            <tr>
                                <th class="row-title">Anno</th>
                                <th>Validazione XSD <span class="dashicons dashicons-info" title="Verifica che il file xml sia tecnicamente corretto alle specifiche XSD"></span></th>
                                <th>Versione XSD</th>
                                <th>Validazione software <span class="dashicons dashicons-info" title="Verifiche aggiuntive del plugin WordPress per il controllo di alcuni campi, come ad esempio le date"></span></th>
                                <th>Data generazione</th>
                                <th>Azione</th>
                            </tr>
                            <?php
                                foreach ( $terms as $term ) {
                                    echo '<tr valign="top" class="alternate">';
                                    echo '<td scope="row">'.$term->name.' <a href="'.avcp_get_basexmlurl($term->name).'" target="_blank" title="'.$term->name.'"><span class="dashicons dashicons-external"></span></a></td>';
                                    
                                    // Formal check
                                    $errors = false;
                                    $xml = new DOMDocument();
                                    $xml->load( avcp_get_basexmlpath( $term->name ) );
                                    echo '<td>';

                                    if ( $term->name >= 2019 ) { // Versione XSD
                                        $xsd_dir = '1_1';
                                        $xsd_ver = 'v 1.1';
                                    } else {
                                        $xsd_dir = '1_0';
                                        $xsd_ver = 'v 1.0';
                                    }
                                    if ($xml->schemaValidate(ABSPATH  . '/wp-content/plugins/italia-anax-xml-bandi-gara/includes/XSD/'.$xsd_dir.'/datasetAppaltiL190.xsd')) {
                                        echo '<span class="dashicons dashicons-yes" title="Corretto"></span>';
                                    } else {
                                        echo '<span class="dashicons dashicons-dismiss" title="Trovati errori"></span>';
                                        $errors = libxml_get_errors();
                                    }
                                    echo '</td>';
                                    echo '<td>'.$xsd_ver.'</td>';
                                    echo '<td>';
                                    query_posts( array( 'post_type' => 'avcp', 'annirif' => $term->name, 'posts_per_page' => '-1') );
                                    global $post;
                                    $erroredate = false;
                                    if ( have_posts() ) : while ( have_posts() ) : the_post();
                                            $datainizio = get_post_meta($post->ID, 'avcp_data_inizio', true);
                                            $datafine = get_post_meta($post->ID, 'avcp_data_fine', true);
                                            if ($datainizio == '' || $datafine == '' ) {
                                                $erroredate = true;
                                                $logerrori .= '[id <b>' . $post->ID . '</b> // ' . get_the_title() . '] -';
                                            }
                                    endwhile; else:
                                    endif;
                                    if ($erroredate == true) {
                                        echo '<br/><font style="background-color:red;color:white;padding:2px;border-radius:3px;font-weight:bold;">ERRORE VALIDAZIONE SOFTWARE: data_inizio / data_fine<br/>' . $logerrori . '</font><br/>';
                                    } else {
                                        echo '<span class="dashicons dashicons-yes"></span>';
                                    }
                                    echo '</td>';
                                    echo '<td>'.( is_file( avcp_get_basexmlpath( $term->name ) ) && filemtime( avcp_get_basexmlpath( $term->name ) ) ? date("d/m/Y H:i", filemtime( avcp_get_basexmlpath( $term->name ) )) : '(non esiste)').'</td>';
                                    echo '<td><input '.( ($term->name < date('Y')-2) ? 'disabled' : '').' type="submit" class="button-primary" name="XMLGEN_'.$term->name.'" value="Genera" /></td>';
                                    echo '</tr>';
                                    if ( $errors ) {
                                        echo '<tr><td colspan="6">';
                                        foreach ($errors as $err) {
                                            print libxml_display_error($err);
                                        }
                                        libxml_clear_errors();
                                        echo '</td></tr>';
                                    }
                                }
                            ?>
                            </table>
                        </form> 
                    </div>
                    <!-- .inside -->

                </div>
                <!-- .postbox -->

            </div>
            <!-- .meta-box-sortables .ui-sortable -->

        </div>
        <!-- post-body-content -->

        <!-- sidebar -->
        <div id="postbox-container-1" class="postbox-container">
            <div class="meta-box-sortables">
                <div class="postbox">
                    <h2><span>Controllo Server</span></h2>
                    <div class="inside">
                        <p><?php
                        $dir = avcp_get_basexmlpath();
                        $file = $dir . '/index.php';
                        $system_ok = true;
                        if(is_dir($dir)) {
                            echo 'Presenza cartella /italia-anax-xml-bandi-gara<font style="color:green;font-weight:bold;"> ==> OK</font>';
                        } else {
                            echo 'Presenza cartella /italia-anax-xml-bandi-gara<font style="color:red;font-weight:bold;"> ==> NON TROVATA</font>';
                            $system_ok = false;
                        }
                        echo '<br/>';
                        if (is_writeable($dir)) {
                            echo 'Permessi scrittura cartella /italia-anax-xml-bandi-gara<font style="color:green;font-weight:bold;"> ==> OK</font>';
                        } else {
                            echo 'Permessi scrittura cartella /italia-anax-xml-bandi-gara<font style="color:red;font-weight:bold;"> ==> NON CORRISPONDENTI</font>';
                            $system_ok = false;
                        }
                        echo '<br/>';
                    
                        if (file_exists($file)) {
                            echo 'Presenza index.php /italia-anax-xml-bandi-gara<font style="color:green;font-weight:bold;"> ==> OK</font>';
                        } else {
                            echo 'Presenza index.php /italia-anax-xml-bandi-gara<font style="color:red;font-weight:bold;"> ==> NON TROVATO</font>';
                            $system_ok = false;
                        }
                        echo '<br/>';
                    
                        $urlcheck = get_site_url() . '/italia-anax-xml-bandi-gara/index.php';
                    
                        $agent = "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_5_8; pt-pt) AppleWebKit/533.20.25 (KHTML, like Gecko) Version/5.0.4 Safari/533.20.27";
                    
                        if(is_callable('curl_init')){
                    
                             // initializes curl session
                             $ch=curl_init();
                    
                             // sets the URL to fetch
                             curl_setopt ($ch, CURLOPT_URL,$urlcheck );
                    
                             // sets the content of the User-Agent header
                             curl_setopt($ch, CURLOPT_USERAGENT, $agent);
                    
                             // return the transfer as a string
                             curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
                    
                             // disable output verbose information
                             curl_setopt ($ch,CURLOPT_VERBOSE,false);
                    
                             // max number of seconds to allow cURL function to execute
                             curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                    
                             curl_exec($ch);
                    
                             // get HTTP response code
                             $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    
                             curl_close($ch);
                    
                            if($httpcode==200) {
                                echo 'Test Accesso pubblico /italia-anax-xml-bandi-gara<font style="color:green;font-weight:bold;"> ==> OK [200]</font>';
                            } else if($httpcode==500) {
                                echo 'Test Accesso pubblico /italia-anax-xml-bandi-gara<font style="color:red;font-weight:bold;"> ==> ERRORE 500 ISE</font>';
                                $headers = get_headers($urlcheck);
                                echo ' - ' . $headers[0];
                                $system_ok = false;
                            } else {
                                echo 'Test Accesso pubblico /italia-anax-xml-bandi-gara<font style="color:red;font-weight:bold;"> ==> ERRORE ' . $httpcode . '</font>';
                                $headers = get_headers($urlcheck);
                                echo ' - ' . $headers[0];
                                $system_ok = false;
                            }
                        } else {
                            echo 'Test Accesso pubblico /italia-anax-xml-bandi-gara<font style="color:red;font-weight:bold;"> ==> CURL_INIT MANCANTE</font>';
                            $system_ok = false;
                        }
                    
                        if ($system_ok) {
                            echo '<hr><center>Nessun problema trovato.</center>';
                        } else {
                            echo '
                            <style>
                            #alert {
                            background: white url(' . plugin_dir_url(__FILE__) . 'includes/alert.jpg) no-repeat center;
                            }
                            </style>';
                            echo 'Sono stati trovati alcuni problemi <b>critici</b>. Affinchè AVCP funzioni correttamente è necessario risolvere al più presto questi problemi. Consultare la documentazione del plugin per conoscere le cause più probabili di questo problema!';
                        }
                        ?></p>
                    </div>
                    <!-- .inside -->
                </div>
                <!-- .postbox -->
            </div>
            <div class="meta-box-sortables">
                <div class="postbox">
                    <h2><form method="post" name="options" target="_self"><input style="float:right;" type="submit" class="button-primary" name="AVCP-RESET-LOG" value="Pulisci" /></form>Log</h2>
                    <div class="inside">
                        <p style="white-space: nowrap;overflow: auto;"><?php echo get_option('anac_log'); ?></p>
                    </div>
                    <!-- .inside -->
                </div>
                <!-- .postbox -->
            </div>
            <!-- .meta-box-sortables -->
        </div>
        <!-- #postbox-container-1 .postbox-container -->
    </div>
    <!-- #post-body .metabox-holder .columns-2 -->
    <br class="clear">
</div>
<!-- #poststuff -->

    <?php
}

function libxml_display_error($error)
{
$return = "<br/>\n";
switch ($error->level) {
case LIBXML_ERR_WARNING:
$return .= "<b>Warning $error->code</b>: ";
break;
case LIBXML_ERR_ERROR:
$return .= "<b>Error $error->code</b>: ";
break;
case LIBXML_ERR_FATAL:
$return .= "<b>Fatal Error $error->code</b>: ";
break;
}
$return .= trim($error->message);
if ($error->file) {
$return .= " in <b>$error->file</b>";
}
$return .= " on line <b>$error->line</b>\n";

return $return;
}

// Enable user error handling
libxml_use_internal_errors(true);

?>
