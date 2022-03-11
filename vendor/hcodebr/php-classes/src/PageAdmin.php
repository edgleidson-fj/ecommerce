<?php

namespace Hcode;

class PageAdmin extends Page{

	public function __construct($opts = array(), $tpl_dir = "/views/admin/"){

		parent::__construct($opts, $tpl_dir); //Reutilizando os código que estão mo método __construct() da classe Pai(Page.php).
	}
}
?>