<?php
/*
 * 会員限定商品プラグイン
 * Copyright (C) 2014,yoshiyuki kawato
 * jazz20471120@gmail.com
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
 */

require_once CLASS_REALDIR . 'helper/SC_Helper_Category.php';

/**
 * カテゴリーを管理するヘルパークラス(拡張).
 *
 * LC_Helper_Category をカスタマイズする場合はこのクラスを編集する.
 *
 * @package Helper
 * @author pineray
 * @version $Id:$
 */
class plg_MemberOnlyProduct_SC_Helper_Category extends SC_Helper_Category
{
    //put your code here
    /**
     * カテゴリーの情報を取得.
     *
     * @param  integer $category_id カテゴリーID
     * @return array
     */
    public function get($category_id)
    {
        $objQuery =& SC_Query_Ex::getSingletonInstance();
        $objCustomer = new SC_Customer_Ex();
        // ログインしている場合としてない場合で表示を切り替える
        if ($objCustomer->isLoginSuccess(true)){
            $col = 'dtb_category.*, dtb_category_total_count.product_count';
            $from = 'dtb_category left join dtb_category_total_count ON dtb_category.category_id = dtb_category_total_count.category_id';
            $where = 'dtb_category.category_id = ? AND del_flg = 0';
            // 登録商品数のチェック
            if ($this->count_check) {
                $where .= ' AND product_count > 0';
            }
        }else{
            $col = 'dtb_category.*, dtb_category_total_count.plg_member_only_product_product_count_non_member as product_count';
            $from = 'dtb_category left join dtb_category_total_count ON dtb_category.category_id = dtb_category_total_count.category_id';
            $where = 'dtb_category.category_id = ? AND del_flg = 0';
            // 登録商品数のチェック
            if ($this->count_check) {
                $where .= ' AND plg_member_only_product_product_count_non_member > 0';
            }
        }

        $arrRet = $objQuery->getRow($col, $from, $where, array($category_id));

        return $arrRet;
    }

    /**
     * カテゴリー一覧の取得.
     *
     * @param  boolean $cid_to_key 配列のキーをカテゴリーIDにする場合はtrue
     * @return array   カテゴリー一覧の配列
     */
    public function getList($cid_to_key = FALSE)
    {
        static $arrCategory = array(), $cidIsKey = array();

        if (!isset($arrCategory[$this->count_check])) {
            $objQuery =& SC_Query_Ex::getSingletonInstance();

            $objCustomer = new SC_Customer_Ex();
                // ログインしている場合としてない場合で表示を切り替える
            if ($objCustomer->isLoginSuccess(true)) {
                $col = 'dtb_category.*, dtb_category_total_count.product_count';
                $from = 'dtb_category left join dtb_category_total_count ON dtb_category.category_id = dtb_category_total_count.category_id';
                // 登録商品数のチェック
                if ($this->count_check) {
                    $where = 'del_flg = 0 AND product_count > 0';
                } else {
                    $where = 'del_flg = 0';
                }
            } else {
                $col = 'dtb_category.*, dtb_category_total_count.plg_member_only_product_product_count_non_member as product_count';
                $from = 'dtb_category left join dtb_category_total_count ON dtb_category.category_id = dtb_category_total_count.category_id';
                // 登録商品数のチェック
                if ($this->count_check) {
                    $where = 'del_flg = 0 AND plg_member_only_product_product_count_non_member > 0';
                } else {
                    $where = 'del_flg = 0';
                }
            }

            $objQuery->setOption('ORDER BY rank DESC');
            $arrTmp = $objQuery->select($col, $from, $where);

            $arrCategory[$this->count_check] = $arrTmp;
        }

        if ($cid_to_key) {
            if (!isset($cidIsKey[$this->count_check])) {
                // 配列のキーをカテゴリーIDに
                $cidIsKey[$this->count_check] = SC_Utils_Ex::makeArrayIDToKey('category_id', $arrCategory[$this->count_check]);
            }

            return $cidIsKey[$this->count_check];
        }

        return $arrCategory[$this->count_check];
    }

}
