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
 * プラグイン の情報クラス.
 *
 * @author yoshiyuki kawato
 */
class plugin_info{
    /** プラグインコード(必須)：プラグインを識別する為キーで、他のプラグインと重複しない一意な値である必要があります */
    static $PLUGIN_CODE       = "MemberOnlyProduct";
    /** プラグイン名(必須)：EC-CUBE上で表示されるプラグイン名. */
    static $PLUGIN_NAME       = "会員限定商品";
    /** クラス名(必須)：プラグインのクラス（拡張子は含まない） */
    static $CLASS_NAME        = "MemberOnlyProduct";
    /** プラグインバージョン(必須)：プラグインのバージョン. */
    static $PLUGIN_VERSION    = "1.0";
    /** 対応バージョン(必須)：対応するEC-CUBEバージョン. */
    static $COMPLIANT_VERSION = "2.13";
    /** 作者(必須)：プラグイン作者. */
    static $AUTHOR            = "yoshiyuki kawato";
    /** 説明(必須)：プラグインの説明. */
    static $DESCRIPTION       = "会員限定商品を扱うことが出来ます。会員限定商品の場合、ログインすると表示されます。";
    /** プラグインURL：プラグイン毎に設定出来るURL（説明ページなど） */
    static $PLUGIN_SITE_URL   = "";
    /** 使用するフックポイント：使用するフックポイントを設定すると、フックポイントが競合した際にアラートが出ます。 */
    static $HOOK_POINTS       = array(
        array('loadClassFileChange','loadClassFileChange'),
        array('prefilterTransform','prefilterTransform'),
        array('LC_Page_Admin_Products_Product_action_before','adminProductsProductActionBefore'),
        array('LC_Page_Admin_Products_Product_action_after','adminProductsProductActionAfter'),
    );
    /** ライセンス */
    static $LICENSE        = "LGPL";
}