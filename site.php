<?php

//usando Namespace
use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;
use \Hcode\Model\User;


//Rota inicial -> http://www.hcodecommerce.com.br:81/     (get)
$app->get('/', function() {
    
	$page = new Page(); //Ao chamar o new Page() ele vai acionar o __construct() com header.html que tem o cabeçalho do template.

	$page->setTpl("index"); //Adicionar o arquivo index.html que tem o corpo do template.

	//Quanto terminar a execução vai ser acionado o __destruct() com o footer.html que tem a tag para fechar o html finalizando o template.
});//Fim Rota.


//Rota com parâmetro -> http://www.hcodecommerce.com.br:81/categories/    (get)
$app->get("/categories/:idcategory", function($idcategory){

	$category =  new Category();

	$category->get((int)$idcategory);

	$page = new Page();

	$page->setTpl("category", [
		"category"=>$category->getValues(),
		"products"=>[]
	]);
});//Fim Rota.


?>