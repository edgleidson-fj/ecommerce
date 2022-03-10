<?php 

require_once("vendor/autoload.php");

use \Slim\Slim; //Usando o namespace Slim.
use \Hcode\Page;//Usando o namespace Hcode.

$app = new Slim();

$app->config('debug', true);

$app->get('/', function() {
    
	$page = new Page(); //Ao chamar o new Page() ele vai acionar o __construct() com header.html que tem o cabeçalho do template.

	$page->setTpl("index"); //Adicionar o arquivo index.html que tem o corpo do template.

	//Quanto terminar a execução vai ser acionado o __destruct() com o footer.html que tem a tag para fechar o html finalizando o template.
});

$app->run();

 ?>