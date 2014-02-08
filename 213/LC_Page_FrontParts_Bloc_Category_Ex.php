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

require_once CLASS_REALDIR . 'pages/frontparts/bloc/LC_Page_FrontParts_Bloc_Category.php';

/**
 * カテゴリ のページクラス(拡張).
 *
 * LC_Page_FrontParts_Bloc_Category をカスタマイズする場合はこのクラスを編集する.
 *
 * @package Page
 * @author LOCKON CO.,LTD.
 * @version $Id: LC_Page_FrontParts_Bloc_Category_Ex.php 22926 2013-06-29 16:24:23Z Seasoft $
 */
class LC_Page_FrontParts_Bloc_Category_Ex extends LC_Page_FrontParts_Bloc_Category
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
     * メインカテゴリの取得.
     *
     * @param  boolean $count_check 登録商品数をチェックする場合はtrue
     * @return array   $arrMainCat メインカテゴリの配列を返す
     */
    public function lfGetMainCat($count_check = false)
    {
        $objQuery =& SC_Query_Ex::getSingletonInstance();
        $col = '*';
        $from = 'dtb_category left join dtb_category_total_count ON dtb_category.category_id = dtb_category_total_count.category_id';
        // メインカテゴリとその直下のカテゴリを取得する。
        $where = 'level <= 2 AND del_flg = 0';

        $objCustomer = new SC_Customer_Ex();
        // ログインしている場合としてない場合で表示を切り替える
        if ($objCustomer->isLoginSuccess(true)) {
            // 登録商品数のチェック
            if ($count_check) {
                $where .= ' AND product_count > 0';
            }
        } else {
            // 登録商品数のチェック
            if ($count_check) {
                $where .= ' AND plg_member_only_product_product_count_non_member > 0';
            }
        }

        $objQuery->setOption('ORDER BY rank DESC');
        $arrRet = $objQuery->select($col, $from, $where);
        // メインカテゴリを抽出する。
        $arrMainCat = array();
        foreach ($arrRet as $cat) {
            if ($cat['level'] != 1) {
                continue;
            }
            // 子カテゴリを持つかどうかを調べる。
            $arrChildrenID = SC_Utils_Ex::sfGetUnderChildrenArray(
                $arrRet,
                'parent_category_id',
                'category_id',
                $cat['category_id']
            );
            $cat['has_children'] = count($arrChildrenID) > 0;
            $arrMainCat[] = $cat;
        }

        return $arrMainCat;
    }
}
