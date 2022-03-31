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
//Rota -> http://www.hcodecommerce.com.br:81/login   (post)
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


//Esqueci a Senha.
//Rota -> http://www.hcodecommerce.com.br:81/forgot      "Esqueci a Senha"  (get)
$app->get("/forgot", function() {

	$page = new Page();

	$page->setTpl("forgot");	
});//Fim Rota.


//Rota -> http://www.hcodecommerce.com.br:81/forgot       "Esqueci a Senha"  (post)
$app->post("/forgot", function(){

	$user = User::getForgot($_POST["email"], false);  //False -> User Normal,  True -> User Adm.

	header("Location: /forgot/sent");
	exit;
});//Fim Rota POST.


//Rota -> http://www.hcodecommerce.com.br:81/forgot/sent      "Esqueci a Senha - Envio de Email" (get)
$app->get("/forgot/sent", function(){

	$page = new Page();

	$page->setTpl("forgot-sent");
});//Fim Rota.


//Rota -> http://www.hcodecommerce.com.br:81/forgot/reset      "Esqueci a Senha - Alterar Senha" (get)
$app->get("/forgot/reset", function(){

	$user = User::validForgotDecrypt($_GET['code']);

	$page = new Page();

	$page->setTpl("forgot-reset", array(
		"name"=>$user['desperson'],
		"code"=>$_GET['code']
	));
}); //Fim Rota.


//Rota -> http://www.hcodecommerce.com.br:81/forgot/reset      "Esqueci a Senha - Alterar Senha" (post)
$app->post("/forgot/reset", function(){

	$forgot = User::validForgotDecrypt($_POST["code"]);	

	User::setForgotUsed($forgot["idrecovery"]);

	$user = new User();

	$user->get((int)$forgot["iduser"]);

	$password = User::getPasswordHash($_POST["password"]);

	$user->setPassword($password);

	$page = new Page();

	$page->setTpl("forgot-reset-success");
}); //Fim Rota


//Perfil
//Rota -> http://www.hcodecommerce.com.br:81/profile    (get)
$app->get("/profile", function(){

	User::verifyLogin(false);

	$user = User::getFromSession();

	$page = new Page();

	$page->setTpl("profile", [
		'user'=>$user->getValues(),
		'profileMsg'=>User::getSuccess(),
		'profileError'=>User::getError()
	]);
});//Fim Rota.


//Rota -> http://www.hcodecommerce.com.br:81/profile          (post)
$app->post("/profile", function(){

	User::verifyLogin(false);

	//Se o "desperson" não for definido, OU "desperson" for vazio.
	if (!isset($_POST['desperson']) || $_POST['desperson'] === '') {
		User::setError("Preencha o seu nome.");
		header('Location: /profile');
		exit;
	}

	//Se o "desemail" não for definido, OU "desemail" for vazio.
	if (!isset($_POST['desemail']) || $_POST['desemail'] === '') {
		User::setError("Preencha o seu e-mail.");
		header('Location: /profile');
		exit;
	}

	$user = User::getFromSession();

	//Se o "desemail" que veio do POST for diferente "desemail" que está no banco.
	if ($_POST['desemail'] !== $user->getdesemail()) {

		//Se o email já existe.
		if (User::checkLoginExist($_POST['desemail']) === true) {
			User::setError("Este endereço de e-mail já está cadastrado.");
			header('Location: /profile');
			exit;
		}
	}

	//Para evitar "Command Inject". 
	//Ignora o que está sendo passado e pegar os valores que veio do banco.
	$_POST['inadmin'] = $user->getinadmin();
	$_POST['despassword'] = $user->getdespassword();
	$_POST['deslogin'] = $_POST['desemail'];

	$user->setData($_POST);

	$user->update();

	User::setSuccess("Dados alterados com sucesso!");

	header('Location: /profile');
	exit;
});//Fim Rota POST.
?>
