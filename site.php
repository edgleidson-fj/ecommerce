<?php 

//Usando namespace.
use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;
use \Hcode\Model\Cart;
use \Hcode\Model\User;
use \Hcode\Model\Address;


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

//Carrinho
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


//Finalizar Pedido.
//Rota -> http://www.hcodecommerce.com.br:81/checkout (get)
$app->get("/checkout", function(){

	User::verifyLogin(false); //False para indicar que não é Admin.

	$cart = Cart::getFromSession();

	$address = new Address();

	$page = new Page();

	$page->setTpl("checkout", [
		"cart"=>$cart->getValues(),
		"address"=>$address->getValues()
	]);
});//Fim Rota.

//Login
//Rota -> http://www.hcodecommerce.com.br:81/login    (get)
$app->get("/login", function(){

	$page = new Page();

	$page->setTpl("login", [
		"error"=>User::getError(),
		'errorRegister'=>User::getErrorRegister(),
		'registerValues'=>(isset($_SESSION['registerValues'])) ? $_SESSION['registerValues'] : ['name'=>'', 'email'=>'', 'phone'=>'']
	]);
});//Fim Rota.


//Rota -> http://www.hcodecommerce.com.br:81/login (post)
$app->post("/login", function(){

	try {
		User::login($_POST['login'], $_POST['password']);	
	} 
	catch(Exception $e){
		User::setError($e->getMessage());
	}

	header("Location: /checkout");
	exit;
});//Fim Rota POST.


//Desconectar do Login.
//Rota -> http://www.hcodecommerce.com.br:81/logout (get)
$app->get("/logout", function(){

	User::logout();

	header("Location: /login");
	exit;
});//Fim Rota.


//Cadastrar usuário.
//Rota -> (post)
$app->post("/register", function(){
	
	$_SESSION['registerValues'] = $_POST;

	//Se o nome não foi definido no POST, OU nome for vazio. 
	if (!isset($_POST['name']) || $_POST['name'] == '') {
		User::setErrorRegister("Preencha o seu nome.");
		header("Location: /login");
		exit;
	}

	//Se o email não foi definido no POST, OU email for vazio. 
	if(!isset($_POST['email']) || $_POST['email'] == ''){
		User::setErrorRegister("Preencha seu email.");
		header("Location: /login");
		exit;
	}

	//Se a senha não foi definido no POST, OU senha for vazio. 
	if(!isset($_POST['password']) || $_POST['password'] == ''){
		User::setErrorRegister("Preencha sua senha.");
		header("Location: /login");
		exit;
	}

	//Se o login/email já existir no banco de dados. 
	if(User::checkLoginExist($_POST['email']) === true){
		User::setErrorRegister("Esse email já está sendo utilizado.");
		header("Location: /login");
		exit;
	}

	$user = new User();

	$user->setData([
		"inadmin"=>0,                    //0->User Normal.  1->User Adm.
		"deslogin"=>$_POST['email'],
		"desperson"=>$_POST['name'],
		"desemail"=>$_POST['email'],
		"despassword"=>$_POST['password'],
		"nrphone"=>$_POST['phone']
	]);

	$user->save();

	User::login($_POST['email'], $_POST['password']);

	header("Location: /checkout");
	exit;
});//Fim Rota POST.
?>
