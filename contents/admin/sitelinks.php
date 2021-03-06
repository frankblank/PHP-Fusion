<?php
namespace PHPFusion\Administration;
/**
 * Class Sitelinks
 *
 * @package PHPFusion\Administration
 * @todo
 *          Toast to follow screen
 *
 */
class Sitelinks {

    private static $instance = NULL;

    private $aidlink = '';

    private $locale = [];

    private $menu_id = "M1";

    private function __construct() {
    }

    /**
     * @return static|null
     */
    public static function getInstance() {
        if (empty(self::$instance)) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    /**
     * Public method for display
     */
    public function admin() {
        $this->aidlink = fusion_get_aidlink();
        $this->locale = fusion_get_locale("", LOCALE.LOCALESET."admin/sitelinks.php");
        pageAccess("SL");
        opentable($this->locale['SL_0100']);
        $this->_admin();
        closetable();
    }

    /**
     * Private method for display
     */
    private function _admin() {
        try {
            $this->upgrade();
        } catch (\Exception $e) {
            set_error(E_USER_NOTICE, $e->getMessage(), $e->getFile(), $e->getLine(), $this->locale["SL_0101"]);
        }

        if (check_get("status")) {
            switch (get("status")) {
                case "menu_rm":
                    add_notice("success", $this->locale["SL_0200"]);
                    break;
                case "menu_add":
                    add_notice("success", $this->locale["SL_0201"]);
                    break;
            }
            redirect(clean_request("", ["status"], FALSE));
        }

        if (get("action") == "new") {
            $this->menu_id = 0;
            $link_tree = array();
            $menu_data = array(
                "menu_name"      => "",
                "links_bbcode"   => "",
                "links_grouping" => FALSE,
                "links_per_page" => 8
            );
        } else {
            if (check_get("menu")) {
                $menu = get("menu");
                if (in_array($menu, array("M1", "M2", "M3"))) {
                    $this->menu_id = $menu;
                } else {
                    if (!dbcount("(menu_id)", DB_SITE_MENUS, "menu_id=:mid", [":mid" => (int)$menu])) {
                        add_notice("danger", $this->locale["SL_0202"]);
                        redirect(ADMIN."site_links.php".fusion_get_aidlink());
                    }
                }
            }
            $this->menu_id = get('menu', FILTER_VALIDATE_INT) ?: $this->menu_id;
            $link_tree = dbquery_tree_full(DB_SITE_LINKS, "link_id", "link_cat", "WHERE link_position='".$this->menu_id."' ORDER BY link_order ASC");
            $menu_data = \PHPFusion\SiteLinks::getSettings($this->menu_id);
        }

        $info = array(
            "menu_form"    => $this->menu_selector(),
            "menu_forms"   => $this->menu_forms($this->menu_id),
            "menu_heading" => $this->menu_heading($menu_data),
            "menu_list"    => $this->menu_list($link_tree),
            "menu_footer"  => $this->menu_footer($link_tree, $menu_data)
        );

        echo fusion_render(ADMIN_TEMPLATES, "admin-sitelinks.twig", $info, TRUE);

        $add_success = json_encode(array(
            "toast"       => TRUE,
            "title"       => $this->locale["SL_0102"],
            "description" => $this->locale["SL_0103"],
            "icon"        => "fas fa-link",
        ));
        $new_fail = json_encode(array(
            "toast"       => TRUE,
            "title"       => $this->locale["SL_0102"],
            "description" => $this->locale["SL_0104"],
            "icon"        => "fas fa-link",
        ));
        $update_success = json_encode(array(
            "toast"       => TRUE,
            "title"       => $this->locale["SL_0102"],
            "description" => $this->locale["SL_0105"],
            "icon"        => "fas fa-link",
        ));
        $remove_success = json_encode(array(
            "toast"       => TRUE,
            "title"       => $this->locale["SL_0102"],
            "description" => $this->locale["SL_0106"],
            "icon"        => "fas fa-link",
        ));

        $menu_update_success = json_encode(array(
            "toast"       => TRUE,
            "title"       => $this->locale["SL_0102"],
            "description" => $this->locale["SL_0107"],
            "icon"        => "fas fa-link",
        ));


        // Update link
        $cookie = cookie(COOKIE_PREFIX.'user');

        echo "<script src='".CONTENTS."js/admin-post.js'></script>";
        echo "<script>
            /** Create new menu link */            
            $(document).on('click', 'a[data-action=\"new\"]', function(e) {
                // generate new menu
                e.preventDefault();
                // clear sortable list.
                $('.sortable').html('');
                // reset menu title
                $('#links_menu').val(0);
                // reset menu heading textbox
                $('#menu_name').prop('readonly', false).prop('disabled', false).val('');
                // reset menu name
                $('#links_menu_name').val('');       
                // set bbcode settings to 0
                $('#links_bbcode-0').prop('checked', true); 
                // set grouping settings to 0
                $('#links_grouping-0').prop('checked', true);
                // hide links per page
                $('#lpp').hide();
                // set links per page to 8
                $('#links_per_page').val(8);
                
                /** Constructor for JS post */
                let newMenu = new FusionPost('sortlinks_form', '".cookie(COOKIE_PREFIX.'user')."', 'SL', 'update-menu');
                newMenu.submit()
                .then(function(response){
                    return newMenu.return();
                })
                .then(function(xhr){
                  let item = JSON.parse(xhr['responseText']);                           
                  if (item['menu_id']) {
                    // redirect to new url
                    document.location.href= '".ADMIN."site_links.php".fusion_get_aidlink()."&menu='+item['menu_id']+'&status=menu_add';                        
                  }                         
                })  
             /** When sanitization fails */
            .catch(function (error){
                    // you can do a popper here or something depending on what you need.
                    console.log('Something went wrong', error);
                    newMenu.showNotice('danger', $new_fail);          
                });                         
            });
            
            $(document).on('click', 'a[data-action=\"remove_menu\"]', function(e) {
                e.preventDefault();
                if (confirm('".$this->locale["SL_0108"]."')) {
                    let form_id = $(this).closest('form').attr('id');                                                
                    let menuAction = new FusionPost(form_id, '$cookie', 'SL', 'remove-menu');
                     menuAction.submit()
                     .then(function(response){
                         return menuAction.return();
                     })
                     .then(function(xhr){
                        let response = JSON.parse(xhr['responseText']);   
                        if (response['status'] === 'Success') {
                            document.location.href= '".ADMIN."site_links.php".fusion_get_aidlink()."&status=menu_rm';    
                        }                                                             
                     })
                     .catch(function(error){
                            console.log('Something went wrong', error);
                     });
                }
                return FALSE;                                
            });

            /** Add links list */
            $(document).on('submit', 'form#customlinksFrm', function (e) {
                // prevent php submit
                e.preventDefault();
                /** Constructor for JS post */
                let submitCustomLinks = new FusionPost('customlinksFrm', '".cookie(COOKIE_PREFIX.'user')."', 'SL', 'add-links');
                /** Do a submit for sanitization */
                submitCustomLinks.submit()
            /** when submit is possible, do the php hook-init Ajax request */
            .then(function (response){
                //console.log(response);
                return submitCustomLinks.return ();
            })
            /** We will get our hook response output */
            .then(function (xhr){
                //console.log(xhr);
                let item = xhr['responseText'];
                $('ol.sortable').append(item);
                $('#link_name').val('');
                $('#link_url').val('');

                submitCustomLinks.resetButton();
                // do a popper
                submitCustomLinks.showNotice('success', $add_success);
            })
            /** When sanitization fails */
            .catch(function (error){
                    // you can do a popper here or something depending on what you need.
                    console.log('Something went wrong', error);
                });
        });

            /** Update site links */
            $(document).on('click', 'button[name=\"save_link\"]', function(e){
                e.preventDefault();
                let form = $(this).closest('form');
                let form_id = form.prop('id');
                // admin post...
                let links = new FusionPost(form_id, '$cookie', 'SL', 'update-links');
                links.submit()
                .then(function(response){
                    return links.return();
                })
                .then(function(xhr){
                    let response = JSON.parse(xhr['responseText']);
                    if (response['status'] === true && response['target']) {
                        $('#'+response['target']).removeClass('show');
                        // trigger toast
                        links.showNotice('success', $update_success);
                        // now trigger the toast
                        links.resetButton();

                    }
                })
                .catch(function(error){
                    console.log('Something went wrong', error);
                });
            });

            /** Title changes on keyup */
            $(document).on('keyup input paste', 'input[name=\"_name\"]', function(e) {
                let panelTitle = $(this).closest('.panel-default').find('.collapse-title');
                panelTitle.text($(this).val());
            });
            /** Links per page Event */
            let lpp = $('#lpp');
            if ( $('#links_grouping').val()) {
                lpp.show();
            }
            $('#links_grouping-0').bind('click', function(e){ lpp.slideUp(); });
            $('#links_grouping-1').bind('click', function(e){ lpp.slideDown(); });

            /** Remove link from menu */
            $(document).on('click', 'a[data-action=\"remove\"]', function(e) {
                e.preventDefault();
                let form_id = $(this).closest('form').attr('id');
                let link_id = $(this).data('id');
                let link_action = new FusionPost(form_id, '$cookie', 'SL', 'remove-links');
                 link_action.submit()
                 .then(function(response){
                     return link_action.return();
                 })
                 .then(function(xhr){
                    let response = xhr['responseText'];
                    // menuItem_121
                    $('#menuItem_'+link_id).remove();
                    link_action.showNotice('success', $remove_success);
                 })
                 .catch(function(error){
                        console.log('Something went wrong', error);
                 });
            });

            /** Save menu */
            $(document).on('click', 'button[name=\"save_menu\"]', function(e){
                e.preventDefault();
                let form = $(this).closest('form');
                let form_id = form.prop('id');
                let menu_name = $('#menu_name').val();
                $('#links_menu_name').val( menu_name );
                // admin post...
                let menuAction = new FusionPost(form_id, '$cookie', 'SL', 'update-menu');
                menuAction.submit()
                    .then(function(response){
                        return menuAction.return();
                    })
                    .then(function(xhr){
                        let response = xhr['responseText'];
                        //console.log(response);
                        menuAction.resetButton();
                        menuAction.showNotice('success', $menu_update_success)
                    })
                    .catch(function(error){
                        console.log('Something went wrong', error);
                    });
            });
        </script>";
    }

    /**
     * @throws \Exception
     */
    public function upgrade() {
        // update all core id ones.
        dbquery("UPDATE ".DB_SITE_LINKS." SET link_position='M1' WHERE link_position='1'");
        dbquery("UPDATE ".DB_SITE_LINKS." SET link_position='M2' WHERE link_position='2'");
        dbquery("UPDATE ".DB_SITE_LINKS." SET link_position='M3' WHERE link_position='3'");
        // for these results, create new menu
        $result = dbquery("SELECT * FROM ".DB_SITE_LINKS." WHERE link_position > 3 GROUP BY link_position");
        if (dbrows($result)) {
            while ($data = dbarray($result)) {
                /** Create a menu for this link_position if does not exist */
                if (!dbcount("(menu_id)", DB_SITE_MENUS, "menu_id=:pos", array(":pos" => $data["link_position"]))) {
                    $settings_array = array(
                        "menu_id"             => 0,
                        "menu_bbcode"         => FALSE,
                        "menu_grouping"       => FALSE,
                        "menu_links_per_page" => 8
                    );
                    $id = dbquery_insert(DB_SITE_MENUS, $settings_array, "save");
                    // now update all the links
                    dbquery("UPDATE ".DB_SITE_LINKS." SET link_position=:new WHERE link_position=:pos", array(
                        ":new" => (int)$id,
                        ":pos" => (int)$data["link_position"]
                    ));
                }
            }
        }
        $settings = fusion_get_settings();
        $new_positions = array("M1", "M2", "M3");
        foreach ($new_positions as $values) {
            if (!isset($settings["links_grouping_".$values])) {
                $settings_array = array(
                    "links_bbcode_".$values   => FALSE,
                    "links_grouping_".$values => FALSE,
                    "links_per_page_".$values => 8
                );
                dbquery_update_settings($settings_array);
            }
        }
        // remove old settings
        dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='links_grouping'");
        dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='links_bbcode'");
        dbquery("DELETE FROM ".DB_SETTINGS." WHERE settings_name='links_per_page'");
    }

    /**
     * @return string
     */
    private function menu_selector() {
        if (check_post('menu')) {
            $menu = post("menu");
            redirect(ADMIN."site_links.php".fusion_get_aidlink()."&amp;menu=$menu");
        }

        return openform("menufrm", "post", FORM_REQUEST, array("inline" => TRUE))
            .form_select("menu", $this->locale["SL_0300"], get("menu"), [
                "options"         => \PHPFusion\SiteLinks::get_SiteLinksPosition(),
                "select_alt"      => TRUE,
                "inline"          => TRUE,
                "class"           => "mr-3",
                "inner_width"     => "300px",
                "label_class"     => "text-nowrap col-sm-4",
                "container_class" => "col-sm-6"
            ])
            .form_button("select_menu", $this->locale["SL_0301"], "select_menu", array("class" => "btn-primary mr-3")).
            ' <a data-action="new" href="'.clean_request("action=new", array("action"), FALSE).'">'.$this->locale["SL_0302"].'</a>'
            .closeform();
    }

    /**
     * @param $menu_id
     *
     * @return string
     */
    private function menu_forms($menu_id) {
        $html = opencollapse('menu-switch');
        $html .= opencollapsebody('News', 'menu-news', 'menu-switch', FALSE);
        $html .= closecollapsebody();
        $html .= opencollapsebody($this->locale["SL_0303"], 'menu-1', 'menu-switch', TRUE);
        $html .= openform('customlinksFrm', 'post', FORM_REQUEST, ['class' => 'form-horizontal']);
        $html .= form_text('link_name', $this->locale["SL_0304"], '', ['required' => TRUE, 'inline' => FALSE]);
        $html .= form_text('link_url', $this->locale["SL_0305"], '', ['required' => FALSE, 'inline' => FALSE]);
        $html .= form_hidden('link_position', '', $menu_id, ['required' => FALSE, 'inline' => FALSE]);
        $html .= "<div class='text-right'>";
        $html .= form_button('link_add', $this->locale["SL_0306"], "", ['class' => 'btn-primary']);
        $html .= "</div>";
        $html .= closeform();
        $html .= closecollapsebody().closecollapse();

        return $html;
    }

    /**
     * @param $settings
     *
     * @return mixed
     */
    private function menu_heading($settings) {
        return form_text("menu_name", $this->locale["SL_0307"], $settings["menu_name"], array(
            "placeholder" => $this->locale["SL_0307"],
            "required"    => TRUE,
            "inline"      => TRUE,
            "class"       => "m-t-10 align-self-center col-12 col-xl-6 pl-0",
            "inner_width" => "300px",
            "deactivate"  => ($this->menu_id && in_array($this->menu_id, array("M1", "M2", "M3")) ? TRUE : FALSE)
        ));

    }

    private function getLinkForm($data) {
        $link_id = $data["link_id"];
        echo opencollapse($link_id);
        echo opencollapsebody($data["link_name"], $link_id."m", $link_id);
        echo openform("_form", "post", FORM_REQUEST, array("form_id" => "_form_".$link_id));
        switch ($data["link_type"]) {
            case "link":
            default:
                echo form_hidden("_type", "", "links", array("input_id" => "_type_".$link_id));
                echo form_text("_url", $this->locale["SL_0305"], $data["link_url"], array("input_id" => "_url_".$link_id, "required" => FALSE));
                echo form_text("_name", $this->locale["SL_0304"], $data["link_name"], array("input_id" => "_name_".$link_id, "required" => TRUE));
                echo form_text("_title", $this->locale["SL_0308"], $data["link_title"], array("input_id" => "_title_".$link_id, "required" => FALSE));
                echo form_textarea("_description", $this->locale["SL_0309"], $data["link_description"], array("input_id" => "_description_".$link_id, "ext_tip" => "The description will be displayed in the menu if the current theme supports it."));
        }
        echo form_hidden("_position", "", $data["link_position"], array("input_id" => "_position_".$link_id));
        echo form_hidden("_id", "", $link_id, array("input_id" => "_lid_".$link_id));
        echo form_text("_icon", $this->locale["SL_0310"], $data["link_icon"], array("input_id" => "_icon_".$link_id));
        echo form_checkbox("_window", $this->locale["SL_0311"], $data["link_window"], array("input_id" => "_window_".$link_id, "type" => "checkbox", "reverse_label" => TRUE));
        echo form_select("_visibility", $this->locale["SL_0312"], $data["link_visibility"], array("input_id" => "_visibility_".$link_id, "options" => fusion_get_groups(), "select_alt" => TRUE));
        echo form_checkbox("_status", $this->locale["SL_0313"], $data["link_status"], array("input_id" => "_status_".$link_id, "type" => "radio", "options" => get_status_opts()));
        echo '<a href="#" class="remove_link text-danger" data-id="'.$link_id.'" data-action="remove">'.$this->locale["SL_0314"].'</a>';
        echo form_button("save_link", $this->locale["SL_0315"], "save_link", array("input_id" => "_save_".$link_id, "class" => "btn-primary"));
        echo closeform();
        echo closecollapsebody();
        echo closecollapse();
    }

    private function recurseList($result, $index = 0) {
        /** check if have results */
        if (isset($result[$index])) {
            /** Loop through current level
             *
             * @var  $link_id -  link_id
             * @var  $link    -  link data
             */
            foreach ($result[$index] as $link_id => $link) {
                echo '<li id="menuItem_'.$link['link_id'].'" style="cursor:pointer;">';
                echo '<div style="max-width:400px;">';
                $this->getLinkForm($link);
                echo '</div>';
                if (isset($result[$link_id])) {
                    echo '<ol style="list-style:none;">';
                    $this->recurseList($result, $link_id);
                    echo '</ol>';
                }
                echo '</li>';
            }
        }
    }

    /**
     * @param $link_tree
     *
     * @return false|string
     */
    private function menu_list($link_tree) {
        add_to_footer("<script src='".INCLUDES."jquery/jquery-ui/jquery-ui.min.js'></script>");
        add_to_footer("<script src='".INCLUDES."jquery/jquery-ui/jquery.mjs.nestedSortable.js'></script>");
        add_to_footer("<script src='".CONTENTS."js/sitelinks-sortable.js'></script>");
        ob_start();
        echo '<ol class="sortable ui-sortable" style="list-style:none;margin:0;padding:0;">';
        $this->recurseList($link_tree);
        echo '</ol>';

        return ob_get_clean();
    }

    /**
     * @param $link_tree
     * @param $settings
     *
     * @return string
     */
    private function menu_footer($link_tree, $settings) {
        // Sort link form
        $locale = fusion_get_locale();
        $html = openform("sortlinks_form", "post", FORM_REQUEST, array("inline" => FALSE, "class" => "spacer-xs"));
        $html .= form_hidden("links_sort", "", "");
        $html .= form_hidden("links_menu", "", $this->menu_id);
        $html .= form_hidden("links_menu_name", "", "");
        $html .= form_checkbox("links_bbcode", $this->locale["SL_0316"], $settings["links_bbcode"], array(
            "options" => get_status_opts(array()),
            "type"    => "radio",
            "inline"  => TRUE,
            "ext_tip" => $locale["SL_0047"]
        ));
        $html .= form_checkbox("links_grouping", $this->locale["SL_0317"], $settings["links_grouping"],
            array(
                "options" => get_status_opts(array(), array(
                    0 => $this->locale["SL_0318"],
                    1 => $this->locale["SL_0319"]
                )),
                "type"    => "radio",
                "inline"  => TRUE,
                "width"   => "250px",
            )
        );
        $html .= '<div id="lpp" class="row" '.(!$settings["links_grouping"] ? 'style="display:none"' : "").'><div class="col-12">';
        $html .= form_text("links_per_page", $this->locale["SL_0320"], $settings["links_per_page"],
            array(
                "type"        => "number",
                "inline"      => TRUE,
                "inner_width" => "200px",
                //"width"    => "250px",
                "required"    => TRUE,
                "placeholder" => $locale["SL_0045"],
                "ext_tip"     => $locale["SL_0044"]
            )
        );
        $html .= "</div></div>";
        if (isnum($this->menu_id)) {
            $html .= '<a class="text-danger btn btn-link" data-id="'.$this->menu_id.'" data-action="remove_menu">'.$this->locale["SL_0314"].'</a>';
        }
        $html .= form_button("save_menu", $this->locale["SL_0321"], "save_menu", ["class" => "btn-primary ml-a"]);
        $html .= closeform();

        return $html;
    }
}
