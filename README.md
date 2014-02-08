ec_cube_member_only_product
===========================
EC-CUBEのプラグイン

会員限定商品を作成する為のプラグインになります。2.13系のみ対応。

使い方
ec_cube_member_only_product配下でtar.gzを作成後管理画面でプラグインを追加する。
tar cvzf ../member_only_product.tar.gz *

プラグイン追加後に下記のファイルを所定の位置に配置して上書きする。
上書きの際に元々カスタマイズした内容を消さないように注意。
downloads/plugin/MemberOnlyProduct/213/LC_Page_FrontParts_Bloc_Category_Ex.php
downloads/plugin/MemberOnlyProduct/213/LC_Page_Products_Detail_Ex.php
downloads/plugin/MemberOnlyProduct/213/LC_Page_Products_List_Ex.php
downloads/plugin/MemberOnlyProduct/213/SC_Helper_DB_Ex.php

管理画面からプラグインを有効にする。

プラグインとしてリリースする予定だったが、EC-CUBEの制約上プラグイン化は不可能だった。

1点目の理由はautoloaderで上書きできない共通クラスが存在する。
SC_ClassAutoloader::autoloadで、下記の制約の為、SC_Helper_DB_Exを上書きできない。
// プラグイン向けフックポイント
// MEMO: プラグインのローダーがDB接続を必要とするため、SC_Queryがロードされた後のみ呼び出される。
//       プラグイン情報のキャッシュ化が行われれば、全部にフックさせることを可能に？

2点目はLCのクラスは処理を追記することは出来ても変更することが出来ない。
一部のfunctionだけ修正したいのに上書きすることが出来ない。
