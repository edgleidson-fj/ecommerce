<?php 

//Usando namespace.
use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;
use \Hcode\Model\Cart;
use \Hcode\Model\User;
use \Hcode\Model\Address;
use \Hcode\Model\Order;
use \Hcode\Model\OrderStatus;


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

	$address = new Address();

	$cart = Cart::getFromSession();

	//Se o CEP não foi definido.
	if (!isset($_GET['zipcode'])) {
		$_GET['zipcode'] = $cart->getdeszipcode();
	}	

	//Se o CEP já foi definido.
	if(isset($_GET['zipcode'])){
		$address->loadFromCEP($_GET['zipcode']);

		$cart->setdeszipcode($_GET['zipcode']);
		$cart->save();
		$cart->getCaculateTotal();
	}

	if (!$address->getdesaddress()) $address->setdesaddress('');
	if (!$address->getdesnumber()) $address->setdesnumber('');
	if (!$address->getdescomplement()) $address->setdescomplement('');
	if (!$address->getdesdistrict()) $address->setdesdistrict('');
	if (!$address->getdescity()) $address->setdescity('');
	if (!$address->getdesstate()) $address->setdesstate('');
	if (!$address->getdescountry()) $address->setdescountry('');
	if (!$address->getdeszipcode()) $address->setdeszipcode('');
	
	$page = new Page();

	$page->setTpl("checkout", [
		"cart"=>$cart->getValues(),
		"address"=>$address->getValues(),
		"products"=>$cart->getProducts(),
		"error"=>Address::getMsgError()
	]);
});//Fim Rota.


//Rota -> http://www.hcodecommerce.com.br:81/checkout   (post)
$app->post("/checkout", function(){

	User::verifyLogin(false); //False -> para indicar que não é user ADM.

	//Se "zipocode" não foi definido OU "zipcode" está vazio.
	if (!isset($_POST['zipcode']) || $_POST['zipcode'] === '') {
		Address::setMsgError("Informe o CEP.");
		header('Location: /checkout');
		exit;
	}
	//Se "desaddress" não foi definido OU "desaddress" está vazio.
	if (!isset($_POST['desaddress']) || $_POST['desaddress'] === '') {
		Address::setMsgError("Informe o endereço.");
		header('Location: /checkout');
		exit;
	}
	//Se "desdistrict" não foi definido OU "desdistrict" está vazio.
	if (!isset($_POST['desdistrict']) || $_POST['desdistrict'] === '') {
		Address::setMsgError("Informe o bairro.");
		header('Location: /checkout');
		exit;
	}
	//Se "descity" não foi definido OU "descity" está vazio.
	if (!isset($_POST['descity']) || $_POST['descity'] === '') {
		Address::setMsgError("Informe a cidade.");
		header('Location: /checkout');
		exit;
	}
	//Se "desstate" não foi definido OU "desstate" está vazio.
	if (!isset($_POST['desstate']) || $_POST['desstate'] === '') {
		Address::setMsgError("Informe o estado.");
		header('Location: /checkout');
		exit;
	}
	//Se "descountry" não foi definido OU "descountry" está vazio.
	if (!isset($_POST['descountry']) || $_POST['descountry'] === '') {
		Address::setMsgError("Informe o país.");
		header('Location: /checkout');
		exit;
	}

	$user = User::getFromSession(); //Pegando user da sessão.
	
	$address = new Address();

	$_POST['deszipcode'] = $_POST['zipcode'];
	$_POST['idperson'] = $user->getidperson();

	$address->setData($_POST);

	$address->save();

	$cart = Cart::getFromSession(); //Pegando o carrinho da sessão.
	
	$cart->getCalculateTotal();

	//Pedido.
	$order = new Order();

	$order->setData([
		'idcart'=>$cart->getidcart(),
		'idaddress'=>$address->getidaddress(),
		'iduser'=>$user->getiduser(),
		'idstatus'=>OrderStatus::EM_ABERTO,
		'vltotal'=>$cart->getvltotal()
	]);

	$order->save();

	$cart->removeSession(); 

	header("Location: /order/".$order->getidorder());
	exit;
});//Fim Rota POST.


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


//Pedido.
//Rota com parâmetro -> http://www.hcodecommerce.com.br:81/order/1   (get)
$app->get("/order/:idorder", function($idorder){

	User::verifyLogin(false);

	$order = new Order();

	$order->get((int)$idorder);

	$page = new Page();

	$page->setTpl("payment", [
		'order'=>$order->getValues()
	]);
});//Fim Rota.


//Boleto.
//Rota com parâmetro -> http://www.hcodecommerce.com.br:81/boleto/1   (get)
$app->get("/boleto/:idorder", function($idorder){

	User::verifyLogin(false);

	$order = new Order();

	$order->get((int)$idorder);

	// DADOS DO BOLETO PARA O SEU CLIENTE
	$dias_de_prazo_para_pagamento = 10;
	$taxa_boleto = 5.00;
	$data_venc = date("d/m/Y", time() + ($dias_de_prazo_para_pagamento * 86400));  // Prazo de X dias OU informe data: "13/04/2006"; 

	$valor_cobrado = formatPrice($order->getvltotal()); // Valor - REGRA: Sem pontos na milhar e tanto faz com "." ou "," ou com 1 ou 2 ou sem casa decimal
	$valor_cobrado = str_replace(".", "", $valor_cobrado);
	$valor_cobrado = str_replace(",", ".",$valor_cobrado);
	$valor_boleto=number_format($valor_cobrado+$taxa_boleto, 2, ',', '');

	$dadosboleto["nosso_numero"] = $order->getidorder();  // Nosso numero - REGRA: Máximo de 8 caracteres!
	$dadosboleto["numero_documento"] = $order->getidorder();	// Num do pedido ou nosso numero
	$dadosboleto["data_vencimento"] = $data_venc; // Data de Vencimento do Boleto - REGRA: Formato DD/MM/AAAA
	$dadosboleto["data_documento"] = date("d/m/Y"); // Data de emissão do Boleto
	$dadosboleto["data_processamento"] = date("d/m/Y"); // Data de processamento do boleto (opcional)
	$dadosboleto["valor_boleto"] = $valor_boleto; 	// Valor do Boleto - REGRA: Com vírgula e sempre com duas casas depois da virgula

	// DADOS DO SEU CLIENTE
	$dadosboleto["sacado"] = $order->getdesperson();
	$dadosboleto["endereco1"] = $order->getdesaddress() . " " . $order->getdesdistrict();
	$dadosboleto["endereco2"] = $order->getdescity() . " - " . $order->getdesstate() . " - " . $order->getdescountry() . " -  CEP: " . $order->getdeszipcode();

	// INFORMACOES PARA O CLIENTE
	$dadosboleto["demonstrativo1"] = "Pagamento de Compra na Loja Hcode E-commerce";
	$dadosboleto["demonstrativo2"] = "Taxa bancária - R$ 0,00";
	$dadosboleto["demonstrativo3"] = "";
	$dadosboleto["instrucoes1"] = "- Sr. Caixa, cobrar multa de 2% após o vencimento";
	$dadosboleto["instrucoes2"] = "- Receber até 10 dias após o vencimento";
	$dadosboleto["instrucoes3"] = "- Em caso de dúvidas entre em contato conosco: suporte@hcode.com.br";
	$dadosboleto["instrucoes4"] = "&nbsp; Emitido pelo sistema Projeto Loja Hcode E-commerce - www.hcode.com.br";

	// DADOS OPCIONAIS DE ACORDO COM O BANCO OU CLIENTE
	$dadosboleto["quantidade"] = "";
	$dadosboleto["valor_unitario"] = "";
	$dadosboleto["aceite"] = "";		
	$dadosboleto["especie"] = "R$";
	$dadosboleto["especie_doc"] = "";


	// ---------------------- DADOS FIXOS DE CONFIGURAÇÃO DO SEU BOLETO --------------- //


	// DADOS DA SUA CONTA - ITAÚ
	$dadosboleto["agencia"] = "1690"; // Num da agencia, sem digito
	$dadosboleto["conta"] = "48781";	// Num da conta, sem digito
	$dadosboleto["conta_dv"] = "2"; 	// Digito do Num da conta

	// DADOS PERSONALIZADOS - ITAÚ
	$dadosboleto["carteira"] = "175";  // Código da Carteira: pode ser 175, 174, 104, 109, 178, ou 157

	// SEUS DADOS
	$dadosboleto["identificacao"] = "Hcode Treinamentos";
	$dadosboleto["cpf_cnpj"] = "24.700.731/0001-08";
	$dadosboleto["endereco"] = "Rua Ademar Saraiva Leão, 234 - Alvarenga, 09853-120";
	$dadosboleto["cidade_uf"] = "São Bernardo do Campo - SP";
	$dadosboleto["cedente"] = "HCODE TREINAMENTOS LTDA - ME";

	// NÃO ALTERAR!
	$path = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "res" . DIRECTORY_SEPARATOR . "boletophp" . DIRECTORY_SEPARATOR . "include" . DIRECTORY_SEPARATOR;

	require_once($path . "funcoes_itau.php");
	require_once($path . "layout_itau.php");
});//Fim Rota.
?>
