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

require_once CLASS_REALDIR . 'helper/SC_Helper_DB.php';

/**
 * DB関連のヘルパークラス(拡張).
 *
 * LC_Helper_DB をカスタマイズする場合はこのクラスを編集する.
 *
 * @package Helper
 * @author LOCKON CO.,LTD.
 * @version $Id: SC_Helper_DB_Ex.php 22856 2013-06-08 07:35:27Z Seasoft $
 */
class SC_Helper_DB_Ex extends SC_Helper_DB
{
    /**
     * カテゴリツリーの取得を行う.
     *
     * @param  integer $parent_category_id 親カテゴリID
     * @param  bool    $count_check        登録商品数のチェックを行う場合 true
     * @return array   カテゴリツリーの配列
     */
    public function sfGetCatTree($parent_category_id, $count_check = false)
    {
        $objCustomer = new SC_Customer_Ex();
        $objQuery =& SC_Query_Ex::getSingletonInstance();
        $col = '';
        $col .= ' cat.category_id,';
        $col .= ' cat.category_name,';
        $col .= ' cat.parent_category_id,';
        $col .= ' cat.level,';
        $col .= ' cat.rank,';
        $col .= ' cat.creator_id,';
        $col .= ' cat.create_date,';
        $col .= ' cat.update_date,';
        $col .= ' cat.del_flg, ';
        // ログインしている場合としてない場合で表示を切り替える
        if ($objCustomer->isLoginSuccess(true)) {
            $col .= ' ttl.product_count';
        } else {
            $col .= ' ttl.plg_member_only_product_product_count_non_member as product_count';
        }
        $from = 'dtb_category as cat left join dtb_category_total_count as ttl on ttl.category_id = cat.category_id';
        // 登録商品数のチェック
        if ($count_check) {
            // ログインしている場合としてない場合で表示を切り替える
            if ($objCustomer->isLoginSuccess(true)) {
                $where = 'del_flg = 0 AND product_count > 0';
            } else {
                $where = 'del_flg = 0 AND plg_member_only_product_product_count_non_member > 0';
            }
        } else {
            $where = 'del_flg = 0';
        }
        $objQuery->setOption('ORDER BY rank DESC');
        $arrRet = $objQuery->select($col, $from, $where);

        $arrParentID = SC_Utils_Ex::getTreeTrail($parent_category_id, 'category_id', 'parent_category_id', $arrRet);

        foreach ($arrRet as $key => $array) {
            foreach ($arrParentID as $val) {
                if ($array['category_id'] == $val) {
                    $arrRet[$key]['display'] = 1;
                    break;
                }
            }
        }

        return $arrRet;
    }

    /**
     * カテゴリツリーの取得を複数カテゴリで行う.
     *
     * @param  integer $product_id  商品ID
     * @param  bool    $count_check 登録商品数のチェックを行う場合 true
     * @return array   カテゴリツリーの配列
     */
    public function sfGetMultiCatTree($product_id, $count_check = false)
    {
        $objCustomer = new SC_Customer_Ex();
        $objQuery =& SC_Query_Ex::getSingletonInstance();
        $col = '';
        $col .= ' cat.category_id,';
        $col .= ' cat.category_name,';
        $col .= ' cat.parent_category_id,';
        $col .= ' cat.level,';
        $col .= ' cat.rank,';
        $col .= ' cat.creator_id,';
        $col .= ' cat.create_date,';
        $col .= ' cat.update_date,';
        $col .= ' cat.del_flg, ';
        // ログインしている場合としてない場合で表示を切り替える
        if ($objCustomer->isLoginSuccess(true)) {
            $col .= ' ttl.product_count';
        }else{
            $col .= ' ttl.plg_member_only_product_product_count_non_member as product_count';
        }
        $from = 'dtb_category as cat left join dtb_category_total_count as ttl on ttl.category_id = cat.category_id';
        // 登録商品数のチェック
        if ($count_check) {
            // ログインしている場合としてない場合で表示を切り替える
            if ($objCustomer->isLoginSuccess(true)) {
                $where = 'del_flg = 0 AND product_count > 0';
            }else{
                $where = 'del_flg = 0 AND plg_member_only_product_product_count_non_member > 0';
            }
        } else {
            $where = 'del_flg = 0';
        }
        $objQuery->setOption('ORDER BY rank DESC');
        $arrRet = $objQuery->select($col, $from, $where);

        $arrCategory_id = SC_Helper_DB_Ex::sfGetCategoryId($product_id);

        $arrCatTree = array();
        foreach ($arrCategory_id as $pkey => $parent_category_id) {
            $arrParentID = SC_Helper_DB_Ex::sfGetParents('dtb_category', 'parent_category_id', 'category_id', $parent_category_id);

            foreach ($arrParentID as $pid) {
                foreach ($arrRet as $key => $array) {
                    if ($array['category_id'] == $pid) {
                        $arrCatTree[$pkey][] = $arrRet[$key];
                        break;
                    }
                }
            }
        }

        return $arrCatTree;
    }

    /**
     * カテゴリツリーの取得を行う.
     *
     * $products_check:true商品登録済みのものだけ取得する
     *
     * @param  string $addwhere       追加する WHERE 句
     * @param  bool   $products_check 商品の存在するカテゴリのみ取得する場合 true
     * @param  string $head           カテゴリ名のプレフィックス文字列
     * @return array  カテゴリツリーの配列
     */
    public function sfGetCategoryList($addwhere = '', $products_check = false, $head = CATEGORY_HEAD)
    {
        $objCustomer = new SC_Customer_Ex();
        $objQuery =& SC_Query_Ex::getSingletonInstance();
        $where = 'del_flg = 0';

        if ($addwhere != '') {
            $where.= " AND $addwhere";
        }

        $objQuery->setOption('ORDER BY rank DESC');

        if ($products_check) {
            $col = 'T1.category_id, category_name, level';
            $from = 'dtb_category AS T1 LEFT JOIN dtb_category_total_count AS T2 ON T1.category_id = T2.category_id';
            // ログインしている場合としてない場合で表示を切り替える
            // $products_check = trueの時しか呼ばれない。管理画面ではfalseが設定される
            if ($objCustomer->isLoginSuccess(true)) {
                $where .= ' AND product_count > 0';
            } else {
                $where .= ' AND plg_member_only_product_product_count_non_member > 0';
            }

        } else {
            $col = 'category_id, category_name, level';
            $from = 'dtb_category';
        }

        $arrRet = $objQuery->select($col, $from, $where);

        $max = count($arrRet);
        $arrList = array();
        for ($cnt = 0; $cnt < $max; $cnt++) {
            $id = $arrRet[$cnt]['category_id'];
            $name = $arrRet[$cnt]['category_name'];
            $arrList[$id] = str_repeat($head, $arrRet[$cnt]['level']) . $name;
        }

        return $arrList;
    }

    /**
     * カテゴリ数の登録を行う.
     *
     *
     * @param  SC_Query $objQuery           SC_Query インスタンス
     * @param  boolean  $is_force_all_count 全カテゴリの集計を強制する場合 true
     * @return void
     */
    public function sfCountCategory($objQuery = NULL, $is_force_all_count = false)
    {
        // 親の処理を実行
        parent::sfCountCategory($objQuery, $is_force_all_count);

        // plg_member_only_product_product_count_non_memberの集計処理のみ下記に追記
        $objProduct = new SC_Product_Ex();

        if ($objQuery == NULL) {
            $objQuery =& SC_Query_Ex::getSingletonInstance();
        }

        $is_out_trans = false;
        if (!$objQuery->inTransaction()) {
            $objQuery->begin();
            $is_out_trans = true;
        }

        //共通のfrom/where文の構築
        $sql_where = SC_Product_Ex::getProductDispConditions('alldtl');
        // 在庫無し商品の非表示
        if (NOSTOCK_HIDDEN) {
            $where_products_class = '(stock >= 1 OR stock_unlimited = 1)';
            $from = $objProduct->alldtlSQL($where_products_class);
        } else {
            $from = 'dtb_products as alldtl';
        }

        //dtb_category_countの構成
        // 各カテゴリに所属する商品の数を集計。集計対象には子カテゴリを含まない。

        //まずテーブル内容の元を取得
        if (!$is_force_all_count) {
            $arrCategoryCountOld = $objQuery->select('category_id,plg_member_only_product_product_count_non_member as product_count','dtb_category_count');
        } else {
            $arrCategoryCountOld = array();
        }

        //各カテゴリ内の商品数を数えて取得
        $sql = <<< __EOS__
            SELECT T1.category_id, COUNT(T2.category_id) as product_count
            FROM dtb_category AS T1
                LEFT JOIN dtb_product_categories AS T2
                    ON T1.category_id = T2.category_id
                LEFT JOIN $from
                    ON T2.product_id = alldtl.product_id
                LEFT JOIN dtb_products AS T3
                    ON alldtl.product_id = T3.product_id AND T3.plg_member_only_product_is_member_only IN(0)
            WHERE $sql_where
            GROUP BY T1.category_id, T2.category_id
__EOS__;

        $arrCategoryCountNew = $objQuery->getAll($sql);
        // 各カテゴリに所属する商品の数を集計。集計対象には子カテゴリを「含む」。
        //差分を取得して、更新対象カテゴリだけを確認する。

        //各カテゴリ毎のデータ値において以前との差を見る
        //古いデータの構造入れ替え
        $arrOld = array();
        foreach ($arrCategoryCountOld as $item) {
            $arrOld[$item['category_id']] = $item['product_count'];
        }
        //新しいデータの構造入れ替え
        $arrNew = array();
        foreach ($arrCategoryCountNew as $item) {
            $arrNew[$item['category_id']] = $item['product_count'];
        }

        unset($arrCategoryCountOld);
        unset($arrCategoryCountNew);

        $arrDiffCategory_id = array();
        //新しいカテゴリ一覧から見て商品数が異なるデータが無いか確認
        foreach ($arrNew as $cid => $count) {
            if ($arrOld[$cid] != $count) {
                $arrDiffCategory_id[] = $cid;
            }
        }
        //削除カテゴリを想定して、古いカテゴリ一覧から見て商品数が異なるデータが無いか確認。
        foreach ($arrOld as $cid => $count) {
            if ($arrNew[$cid] != $count && $count > 0) {
                $arrDiffCategory_id[] = $cid;
            }
        }

        //対象IDが無ければ終了
        if (count($arrDiffCategory_id) == 0) {
            if ($is_out_trans) {
                $objQuery->commit();
            }

            return;
        }

        //差分対象カテゴリIDの重複を除去
        $arrDiffCategory_id = array_unique($arrDiffCategory_id);

        //dtb_category_countの更新 差分のあったカテゴリだけ更新する。
        foreach ($arrDiffCategory_id as $cid) {
            $sqlval = array();
            $sqlval['create_date'] = 'CURRENT_TIMESTAMP';
            $sqlval['plg_member_only_product_product_count_non_member'] = (string) $arrNew[$cid];
            if ($sqlval['plg_member_only_product_product_count_non_member'] =='') {
                $sqlval['plg_member_only_product_product_count_non_member'] = (string) '0';
            }
            $objQuery->update('dtb_category_count', $sqlval, 'category_id = ?', array($cid));
        }

        unset($arrOld);
        unset($arrNew);

        //差分があったIDとその親カテゴリIDのリストを取得する
        $arrTgtCategory_id = array();
        foreach ($arrDiffCategory_id as $parent_category_id) {
            $arrTgtCategory_id[] = $parent_category_id;
            $arrParentID = $this->sfGetParents('dtb_category', 'parent_category_id', 'category_id', $parent_category_id);
            $arrTgtCategory_id = array_unique(array_merge($arrTgtCategory_id, $arrParentID));
        }

        unset($arrDiffCategory_id);

        //dtb_category_total_count 集計処理開始
        //更新対象カテゴリIDだけ集計しなおす。
        $arrUpdateData = array();
        $where_products_class = '';
        if (NOSTOCK_HIDDEN) {
            $where_products_class .= '(stock >= 1 OR stock_unlimited = 1)';
        }
        $from = $objProduct->alldtlSQL($where_products_class);
        foreach ($arrTgtCategory_id as $category_id) {
            $arrWhereVal = array();
            list($tmp_where, $arrTmpVal) = $this->sfGetCatWhere($category_id);
            if ($tmp_where != '') {
                $sql_where_product_ids = 'alldtl.product_id IN (SELECT product_id FROM dtb_product_categories WHERE ' . $tmp_where . ')';
                $arrWhereVal = $arrTmpVal;
            } else {
                $sql_where_product_ids = '0<>0'; // 一致させない
            }
            $where = "($sql_where) AND ($sql_where_product_ids)";

            $arrUpdateData[$category_id] = $objQuery->count("$from JOIN dtb_products AS T_plg_member_only ON alldtl.product_id = T_plg_member_only.product_id AND T_plg_member_only.plg_member_only_product_is_member_only = 0", $where, $arrWhereVal);
        }

        unset($arrTgtCategory_id);

        // 更新対象だけを更新。
        foreach ($arrUpdateData as $cid => $count) {
            $sqlval = array();
            $sqlval['create_date'] = 'CURRENT_TIMESTAMP';
            $sqlval['plg_member_only_product_product_count_non_member'] = $count;
            if ($sqlval['plg_member_only_product_product_count_non_member'] =='') {
                $sqlval['plg_member_only_product_product_count_non_member'] = (string) '0';
            }
            $ret = $objQuery->update('dtb_category_total_count', $sqlval, 'category_id = ?', array($cid));
        }
        // トランザクション終了処理
        if ($is_out_trans) {
            $objQuery->commit();
        }
    }
}
