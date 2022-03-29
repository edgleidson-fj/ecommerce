<?php 

use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;
use \Hcode\Model\Cart;
use \Hcode\Model\User;


//Rota inicial -> http://www.hcodecommerce.com.br:81/     (get)
$app->get('/', function() {

	$products = Product::listAll();

	$page = new Page();//Ao chamar o new Page() ele vai acionar o __construct() com header.html que tem o cabeçalho do template.

	//Adicionar o arquivo index.html que tem o corpo do template.
	$page->setTpl("index", [
		'products'=>Product::checkList($products) //Acessando o checkList() para verificar todos valores dos produtos incluindo as imagens.
	]);

	//Quanto terminar a execução vai ser acionado o __destruct() com o footer.html que tem a tag para fechar o html finalizando o template.
});//Fim Rota.


//Rota com parâmetro -> http://www.hcodecommerce.com.br:81/categories/    (get)
$app->get("/categories/:idcategory", function($idcategory){

	//Saber qual é a página que está no momento.
	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;//Se foi definido a página no GET no URL pega o número, se não, pega a página 1.

	$category = new Category();

	$category->get((int)$idcategory);

	$pagination = $category->getProductsPage($page);

	$pages = [];

	for ($i=1; $i <= $pagination['pages']; $i++) { 
		array_push($pages, [
			'link'=>'/categories/'.$category->getidcategory().'?page='.$i,
			'page'=>$i
		]);
	}

	$page = new Page();

	$page->setTpl("category", [
		'category'=>$category->getValues(),
		'products'=>$pagination["data"],
		'pages'=>$pages
	]);
});//Fim Rota.


//Rota com parâmetro -> http://www.hcodecommerce.com.br:81/products/    (get)
$app->get("/products/:desurl", function($desurl){

	$product = new Product();

	$product->getFromURL($desurl);

	$page = new Page();

	$page->setTpl("product-detail", [
		'product'=>$product->getValues(),
		'categories'=>$product->getCategories()
	]);
});//Fim Rota.


//Rota -> http://www.hcodecommerce.com.br:81/cart   (get)
$app->get("/cart", function(){

	$cart = Cart::getFromSession();

	$page = new Page();

	$page->setTpl("cart", [
		'cart'=>$cart->getValues(),
		'products'=>$cart->getProducts(),
		'error'=>Cart::getMsgError()
	]);
});//Fim Rota.


//Rota -> http://www.hcodecommerce.com.br:81/cart/    "INSERT" (get)
$app->get("/cart/:idproduct/add", function($idproduct){

	$product = new Product();

	$product->get((int)$idproduct);

	$cart = Cart::getFromSession();

	$qtd = (isset($_GET['qtd'])) ? (int)$_GET['qtd'] : 1;

	for ($i = 0; $i < $qtd; $i++) {
		
		$cart->addProduct($product);
	}

	header("Location: /cart");
	exit;
});//Fim Rota.


//Rota -> http://www.hcodecommerce.com.br:81/cart/    "UPDATE"/Remover um item    (get)  
$app->get("/cart/:idproduct/minus", function($idproduct){

	$product = new Product();

	$product->get((int)$idproduct);

	$cart = Cart::getFromSession();

	$cart->removeProduct($product);

	header("Location: /cart");
	exit;
});//Fim Rota.


//Rota -> http://www.hcodecommerce.com.br:81/cart/    "UPDATE"/Remover todos itens    (get)  
$app->get("/cart/:idproduct/remove", function($idproduct){

	$product = new Product();

	$product->get((int)$idproduct);

	$cart = Cart::getFromSession();

	$cart->removeProduct($product, true);//true -> para informar que está removendo todos. Por padrão é false.

	header("Location: /cart");
	exit;
});//Fim Rota.


//Rota -> http://www.hcodecommerce.com.br:81/cart/  (post)
$app->post("/cart/freight", function(){

	$cart = Cart::getFromSession();

	$cart->setFreight($_POST['zipcode']);

	header("Location: /cart");
	exit;
});//Fim Rota POST.

 ?>