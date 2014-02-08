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

require_once CLASS_REALDIR . 'pages/products/LC_Page_Products_List.php';

/**
 * LC_Page_Products_List のページクラス(拡張).
 *
 * LC_Page_Products_List をカスタマイズする場合はこのクラスを編集する.
 *
 * @package Page
 * @author LOCKON CO.,LTD.
 * @version $Id: LC_Page_Products_List_Ex.php 22926 2013-06-29 16:24:23Z Seasoft $
 */
class LC_Page_Products_List_Ex extends LC_Page_Products_List
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
     * 検索条件のwhere文とかを取得
     *
     * @return array
     */
    public function lfGetSearchCondition($arrSearchData)
    {
        $searchCondition = parent::lfGetSearchCondition($arrSearchData);

        $objCustomer = new SC_Customer_Ex();
        // ログインしている場合としてない場合で表示を切り替える
        if ($objCustomer->isLoginSuccess(true)) {
            $searchCondition['where']   .= ' AND alldtl.plg_member_only_product_is_member_only IN(0,1) ';
        } else {
            $searchCondition['where']   .= ' AND alldtl.plg_member_only_product_is_member_only IN(0) ';
        }

        // XXX 一時期内容が異なっていたことがあるので別要素にも格納している。
        $searchCondition['where_for_count'] = $searchCondition['where'];

        return $searchCondition;
    }
}
