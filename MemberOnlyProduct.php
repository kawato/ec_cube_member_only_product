<?php
/*
 * 会員限定商品プラグイン
 * Copyright (C) 2014-01-08,yoshiyuki kawato
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
/**
 * 会員限定商品を設定することが出来ます。
 * @author yoshiyuki kawato
 *
 */
class MemberOnlyProduct extends SC_Plugin_Base {

    /**
     * コンストラクタ
     * プラグイン情報(dtb_plugin)をメンバ変数をセットします.
     * @param array $arrSelfInfo dtb_pluginの情報配列
     * @return void
     */
    public function __construct(array $arrSelfInfo) {
        parent::__construct($arrSelfInfo);
    }

    /**
     * (non-PHPdoc)
     * @see SC_Plugin_Base::install()
     */
    public function install($arrPlugin) {
        // テーブル定義を変更
        $objQuery =& SC_Query_Ex::getSingletonInstance();
        $objQuery->begin();
        switch(DB_TYPE){
            case "pgsql" :
                $objQuery->query("ALTER TABLE dtb_products ADD COLUMN plg_member_only_product_is_member_only smallint default 0");
                $objQuery->query("ALTER TABLE dtb_category_total_count ADD COLUMN plg_member_only_product_product_count_non_member smallint default 0");
                $objQuery->query("ALTER TABLE dtb_category_count ADD COLUMN plg_member_only_product_product_count_non_member smallint default 0");
                break;
            case "mysql" :
                $objQuery->query("ALTER TABLE dtb_products ADD COLUMN plg_member_only_product_is_member_only smallint default 0");
                $objQuery->query("ALTER TABLE dtb_category_total_count ADD COLUMN plg_member_only_product_product_count_non_member smallint default 0");
                $objQuery->query("ALTER TABLE dtb_category_count ADD COLUMN plg_member_only_product_product_count_non_member smallint default 0");
                break;
        }
        $objQuery->commit();

        // アイコン
        copy(PLUGIN_UPLOAD_REALDIR . $arrPlugin['plugin_code'] . "/logo.png", PLUGIN_HTML_REALDIR . $arrPlugin['plugin_code'] . "/logo.png");
    }

    /**
     * (non-PHPdoc)
     * @see SC_Plugin_Base::uninstall()
     */
    public function uninstall($arrPlugin) {
        // テーブル定義を変更
        $objQuery =& SC_Query_Ex::getSingletonInstance();
        $objQuery->query("ALTER TABLE dtb_products DROP plg_member_only_product_is_member_only");
        $objQuery->query("ALTER TABLE dtb_category_total_count DROP plg_member_only_product_product_count_non_member");
        $objQuery->query("ALTER TABLE dtb_category_count DROP plg_member_only_product_product_count_non_member");
    }

    /**
     * (non-PHPdoc)
     * @see SC_Plugin_Base::enable()
     */
    function enable($arrPlugin) {
        // nop
        $objQuery =& SC_Query_Ex::getSingletonInstance();
        if(self::getVersion() >= 2130){

            $objDb = new SC_Helper_DB_Ex();
            // カテゴリ数のカウントを集計
            $objDb->sfCountCategory($objQuery, TRUE);

        }else{

        }

        // dtb_csvにplg_member_only_product_is_member_onlyを追加
        $sqlval = array();
        $dtb_csv_no = $objQuery->nextVal('dtb_csv_no');
        $sqlval['no'] = $dtb_csv_no;
        $sqlval['csv_id'] = 1;
        $sqlval['col'] = 'plg_member_only_product_is_member_only';
        $sqlval['disp_name'] = '会員のみ公開';
        $sqlval['rw_flg'] = CSV_COLUMN_RW_FLG_READ_WRITE;
        $sqlval['status'] = 0;
        $sqlval['create_date'] = "CURRENT_TIMESTAMP";
        $sqlval['update_date'] = "CURRENT_TIMESTAMP";
        $sqlval['mb_convert_kana_option'] = "n";
        $sqlval['size_const_type'] = "INT_LEN";
        $sqlval['error_check_types'] = "NUM_CHECK,MAX_LENGTH_CHECK";
        $objQuery->insert("dtb_csv", $sqlval);
    }

    /**
     * (non-PHPdoc)
     * @see SC_Plugin_Base::disable()
     */
    function disable($arrPlugin) {
        $objQuery = SC_Query_Ex::getSingletonInstance();

        // dtb_csvからplg_member_only_product_is_member_onlyを削除
        $objQuery->delete("dtb_csv","col = ?",array('plg_member_only_product_is_member_only'));
    }

    /**
     * テンプレートを変更する
     * @param string $source
     * @param LC_Page_Ex $objPage
     * @param string $filename
     */
    function prefilterTransform(&$source, LC_Page_Ex $objPage, $filename) {
        $objTransform = new SC_Helper_Transform($source);
        $templateDir = PLUGIN_UPLOAD_REALDIR ."MemberOnlyProduct/templates/";
        switch($objPage->arrPageLayout['device_type_id']) {
            // 端末種別：PC
            case DEVICE_TYPE_PC:
                $templateDir .= "default/";
                break;
                // 端末種別：モバイル
            case DEVICE_TYPE_MOBILE:
                $templateDir .= "mobile/";
                break;
                // 端末種別：スマートフォン
            case DEVICE_TYPE_SMARTPHONE:
                $templateDir .= "sphone/";
                break;
                // 端末種別：管理画面
            case DEVICE_TYPE_ADMIN:
            default:
                $templateDir .= "admin/";
                // 商品登録画面
                if (strpos($filename, "products/product.tpl") !== false) {
//                    $objTransform->select("div#products.contents-main table.form tr",16)->insertAfter(file_get_contents($templateDir . "products/plg_MemberOnlyProduct_product.tpl"));
                    $objTransform->select("div#products.contents-main table.form tr",15)->insertAfter(file_get_contents($templateDir . "products/plg_MemberOnlyProduct_product.tpl"));

                }
                // 商品登録確認画面
                elseif(strpos($filename, "products/confirm.tpl") !== false) {
//                    $objTransform->select("div#products.contents-main table tr",14)->insertAfter(file_get_contents($templateDir . "products/plg_MemberOnlyProduct_confirm.tpl"));
                    $objTransform->select("div#products.contents-main table tr",13)->insertAfter(file_get_contents($templateDir . "products/plg_MemberOnlyProduct_confirm.tpl"));

                }
                break;
        }
        $source = $objTransform->getHTML();
    }

    /**
     * スーパーフックポイント
     *
     * @param LC_Page_EX $objPage
     */
    function  preProcess(LC_Page_EX $objPage){

    }
    /**
     * クラスをオーバーライドする
     * @param string $classname
     * @param string $classpath
     */
    function loadClassFileChange(&$classname, &$classpath){
        if($this->getVersion() >= 2130){
            $templateDir = PLUGIN_UPLOAD_REALDIR . 'MemberOnlyProduct' . DIRECTORY_SEPARATOR . '213' . DIRECTORY_SEPARATOR;
            if($classname == 'SC_Helper_Category_Ex'){
                $classname = 'plg_MemberOnlyProduct_SC_Helper_Category';
                $classpath = $templateDir . $classname . '.php';
            }
        }else{
            $templateDir = PLUGIN_UPLOAD_REALDIR . 'MemberOnlyProduct' . DIRECTORY_SEPARATOR . '212' . DIRECTORY_SEPARATOR;
        }

    }

    /**
     * 商品登録前処理
     * @param LC_Page_Ex $objPage
     */
    function adminProductsProductActionBefore(LC_Page_Ex $objPage)
    {
        $objPage->plg_member_only_product_arrIsMemberOnly = array('1'=>'会員のみ公開','0'=>'非会員にも公開');
    }

    /**
     * 商品登録後処理
     * @param LC_Page_Ex $objPage
     */
    function adminProductsProductActionAfter(LC_Page_Ex $objPage)
    {

        $objFormParam = new SC_FormParam_Ex();

        switch($objPage->getMode($objPage)) {
            case "pre_edit":
            case "copy" :
                break;
            case "edit":
                // パラメーター初期化, 取得
                $this->lfInitFormParamAdminProductsProduct($objFormParam, $_POST);
                $arrForm = $objFormParam->getHashArray();
                // エラーチェック
                $arrErr = $this->lfCheckError($objFormParam, $arrForm);
                $objPage->arrErr = array_merge((array)$objPage->arrErr, (array)$arrErr);
                $objPage->arrForm = array_merge((array)$objPage->arrForm, (array)$arrForm);
                if (count($objPage->arrErr) > 0) {
                    $this->goBackProduct($objPage);
                }
                break;
            case "complete":
                // パラメーター初期化, 取得
                $this->lfInitFormParamAdminProductsProduct($objFormParam, $_POST);
                $arrForm = $objFormParam->getHashArray();
                // エラーチェック
                $arrErr = $this->lfCheckError($objFormParam, $arrForm);
                $objPage->arrErr = array_merge((array)$objPage->arrErr, (array)$arrErr);
                $objPage->arrForm = array_merge((array)$objPage->arrForm, (array)$arrForm);
                if (count($objPage->arrErr) == 0) {
                    $objQuery =& SC_Query_Ex::getSingletonInstance();
                    $sqlval = array();
                    if($objPage->arrForm['plg_member_only_product_is_member_only'] == 1){
                        $sqlval['plg_member_only_product_is_member_only'] = 1;
                    }else{
                        $sqlval['plg_member_only_product_is_member_only'] = 0;
                    }
                    $sqlval['update_date'] = "CURRENT_TIMESTAMP";
                    $objQuery->update('dtb_products', $sqlval, 'product_id = ?', array($objPage->arrForm['product_id']));
                } else {
                    $this->goBackProduct($objPage);
                }
                break;
            default:
                // パラメーター初期化, 取得
                $this->lfInitFormParamAdminProductsProduct($objFormParam, $_POST);
                $arrForm = $objFormParam->getHashArray();
                if ($arrForm['plg_member_only_product_is_member_only'] == '') {
                    // 初期値：非会員にも公開
                    $arrForm['plg_member_only_product_is_member_only'] = 0;
                }

                $objPage->arrForm = array_merge((array) $objPage->arrForm, (array) $arrForm);
                break;
        }
    }

    /**
     *  AdminProducts用パラメーター情報の初期化
     *
     * @param object $objFormParam SC_FormParamインスタンス
     * @param array $arrPost $_POSTデータ
     * @return void
     */
    function lfInitFormParamAdminProducts(&$objFormParam, $arrPost) {
    }


    /**
     * AdminProductsProduct用パラメーター情報の初期化
     *
     * @param object $objFormParam SC_FormParamインスタンス
     * @param array $arrPost $_POSTデータ
     * @return void
     */
    function lfInitFormParamAdminProductsProduct(&$objFormParam, $arrPost) {
        $objFormParam->addParam("会員のみ公開", "plg_member_only_product_is_member_only", INT_LEN, 'n', array("NUM_CHECK", "MAX_LENGTH_CHECK"));

        $objFormParam->setParam($arrPost);
        $objFormParam->convParam();
    }

    /**
     * フォーム入力パラメーターのエラーチェック<br/>
     *
     * @param object $objFormParam SC_FormParamインスタンス
     * @param array $arrForm フォーム入力パラメーター配列
     * @return array エラー情報を格納した連想配列
     */
    function lfCheckError(&$objFormParam, $arrForm) {
        $objErr = new SC_CheckError_Ex($arrForm);
        // 入力パラメーターチェック
        $arrErr = $objFormParam->checkError();

        $arrErr = array_merge((array)$arrErr, (array)$objErr->arrErr);
        return $arrErr;
    }

    /**
     * Product画面へ戻る
     *
     * @param LC_Page_Admin_Products $objPage 商品管理のページクラス
     * @return void
     */
    function goBackProduct(&$objPage) {
        // アップロードファイル情報の初期化
        $objUpFile = new SC_UploadFile_Ex(IMAGE_TEMP_REALDIR, IMAGE_SAVE_REALDIR);
        $objPage->lfInitFile($objUpFile);
        $objUpFile->setHiddenFileList($_POST);
        // ダウンロード販売ファイル情報の初期化
        $objDownFile = new SC_UploadFile_Ex(DOWN_TEMP_REALDIR, DOWN_SAVE_REALDIR);
        $objPage->lfInitDownFile($objDownFile);
        $objDownFile->setHiddenFileList($_POST);
        // 入力画面表示設定
        $objPage->tpl_mainpage = 'products/product.tpl';
        $objPage->arrForm = $objPage->lfSetViewParam_InputPage($objUpFile, $objDownFile, $objPage->arrForm);
        // ページonload時のJavaScript設定
        $objPage->tpl_onload = $objPage->lfSetOnloadJavaScript_InputPage();
    }

    /**
     * EC-CUBEのバージョンを取得する
     *
     * @return number
     */
    public static function getVersion(){
        return (int)str_replace('.','',ECCUBE_VERSION);
    }
}