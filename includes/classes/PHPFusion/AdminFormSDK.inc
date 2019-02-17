<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: AdminFormSDK.inc
| Author: Frederick MC Chan
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion;

/**
 * Interface AdminFormSDK
 *
 * @package PHPFusion
 */
interface AdminFormSDK {

    public function properties();

    public function data();

    /**
     * @return mixed
     *
     *              Field names for meta.
     *              status
     *              visibility
     *              startdate
     *              enddate
     *
     *
     */
    public function fields();

    public function custom($data);

    public function save($data);

    public function update($data);

}