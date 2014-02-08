<?php
/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) 2000-2013 LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

require_once CLASS_REALDIR . 'pages/products/LC_Page_Products_Detail.php';

/**
 * LC_Page_Products_Detail のページクラス(拡張).
 *
 * LC_Page_Products_Detail をカスタマイズする場合はこのクラスを編集する.
 *
 * @package Page
 * @author LOCKON CO.,LTD.
 * @version $Id: LC_Page_Products_Detail_Ex.php 22926 2013-06-29 16:24:23Z Seasoft $
 */
class LC_Page_Products_Detail_Ex extends LC_Page_Products_Detail
{
    /**
     * Page を初期化する.
     *
     * @return void
     */
    function init()
    {
        parent::init();
    }

    /**
     * Page のプロセス.
     *
     * @return void
     */
    function process()
    {
        parent::process();
    }

    /**
     * プロダクトIDのみのチェックで会員のみ商品か判断してはじく<br/>
     * 以降の処理で特にチェックは行わない。
     * (non-PHPdoc)
     * @see LC_Page_Products_Detail::lfCheckProductId()
     */
    public function lfCheckProductId($admin_mode, $product_id)
    {
        // 管理機能からの確認の場合は、非公開の商品も表示する。
        if (isset($admin_mode) && $admin_mode == 'on') {
            SC_Utils_Ex::sfIsSuccess(new SC_Session_Ex());
            $status = true;
            $where = 'del_flg = 0';
        } else {
            $status = false;
            $where = 'del_flg = 0 AND status = 1';
            // 会員クラス
            $objCustomer = new SC_Customer_Ex();
            // ログイン判定
            if ($objCustomer->isLoginSuccess() === false) {
                $where .= ' AND plg_member_only_product_is_member_only = 0';
            }
        }

        if (!SC_Utils_Ex::sfIsInt($product_id)
        || SC_Utils_Ex::sfIsZeroFilling($product_id)
        || !SC_Helper_DB_Ex::sfIsRecord('dtb_products', 'product_id', (array) $product_id, $where)
        ) {
            SC_Utils_Ex::sfDispSiteError(PRODUCT_NOT_FOUND);
        }

        return $product_id;
    }
}
