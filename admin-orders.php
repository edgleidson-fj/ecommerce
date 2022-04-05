<?php 

//Usando namespace.
use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Order;
use \Hcode\Model\OrderStatus;


//Rota com parâmetro -> http://www.hcodecommerce.com.br:81/admin/orders/1/status     "UPDATE - Alterar Status"  (get)
$app->get("/admin/orders/:idorder/status", function($idorder){

	User::verifyLogin();

	$order = new Order();
	$order->get((int)$idorder);

	$page = new PageAdmin();

	$page->setTpl("order-status", [
		'order'=>$order->getValues(),
		'status'=>OrderStatus::listAll(),
		'msgSuccess'=>Order::getSuccess(),
		'msgError'=>Order::getError()
	]);

	Order::clearSuccess();
	Order::clearError();
});//Fim Rota.


//Rota com parâmetro -> http://www.hcodecommerce.com.br:81/admin/orders/1/status    "UPDATE - Alterar Status" (post)
$app->post("/admin/orders/:idorder/status", function($idorder){

	User::verifyLogin();

	//Se o "idstatus" não for definido OU o "idstatus" não for maior que 0.
	if (!isset($_POST['idstatus']) || !(int)$_POST['idstatus'] > 0) {
		Order::setError("Informe o status atual.");
		header("Location: /admin/orders/".$idorder."/status");
		exit;
	}	

	$order = new Order();
	$order->get((int)$idorder);
	$order->setidstatus((int)$_POST['idstatus']);
	$order->save();

	Order::setSuccess("Status atualizado.");
	header("Location: /admin/orders/".$idorder."/status");
	exit;
});//Fim Rota POST.


//Rota com parâmetro -> http://www.hcodecommerce.com.br:81/admin/orders/1/delete     "DELETE" (get)
$app->get("/admin/orders/:idorder/delete", function($idorder){

	User::verifyLogin();

	$order = new Order();
	$order->get((int)$idorder);
	$order->delete();

	header("Location: /admin/orders");
	exit;
});//Fim Rota.


//Rota com parâmetro -> http://www.hcodecommerce.com.br:81/admin/orders/1     "Detalhe do Pedido" (get)
$app->get("/admin/orders/:idorder", function($idorder){

	User::verifyLogin();

	$order = new Order();
	$order->get((int)$idorder);

	$cart = $order->getCart(); //Pegando o carrinho do pedido.

	$page = new PageAdmin();

	$page->setTpl("order", [
		'order'=>$order->getValues(),
		'cart'=>$cart->getValues(),
		'products'=>$cart->getProducts()
	]);
});//Fim Rota.


//Rota -> http://www.hcodecommerce.com.br:81/admin/orders     "Lista tudo" (get)
$app->get("/admin/orders", function(){

	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("orders", [
		"orders"=>Order::listAll()
	]);
});//Fim Rota.

?>