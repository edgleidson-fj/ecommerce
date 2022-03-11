<?php

namespace Hcode;

use Rain\tpl; //Usando o namespace Rain.

class Page{

	private $tpl;
	private $options =[];
	private $defaults = [ 'dados'=>[] ];

	//Método mágico para ser executado no início.
	public function __construct($opts = array(), $tpl_dir = "/views/"){

		//Mesclar os Arrays.
		$this->options = array_merge($this->defaults, $opts); // O Array($opts) vai sobre escrever o Array($defaults) na varíavel $options. 

		$config = array(
			"tpl_dir"       => $_SERVER['DOCUMENT_ROOT'] . $tpl_dir,
			"cache_dir"     => $_SERVER['DOCUMENT_ROOT'] . "/views-cache/",
			"debug"         => false
		);

		Tpl::configure( $config );

		$this->tpl = new Tpl;

		$this->setDados($this->options['dados']);

		$this->tpl->draw("header"); //Desenhar o template na parte do Cabeçalho.

	}//Fim do Construct.


	private function setDados($dados = array()){

		foreach ($dados as $key => $value) {
			$this->tpl->assign($key, $value);
		}//Fim do Foreach.
	}


	//Conteúdo do corpo.
	public function setTpl($name, $dados = array(), $returnHTML = false){

		$this->setDados($dados);

		return $this->tpl->draw($name, $returnHTML); //Desenhar o template na parte do corpo.
	}


	//Método mágico para ser executado no final.
	public function __destruct(){
		$this->tpl->draw("footer"); //Desenhar o template na parte do Rodapé.
	}//Fim do Destruct.
}


?>