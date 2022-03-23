<?php

//Formatar para valor em Real.
function formatPrice(float $vlprice){

	return number_format($vlprice, 2, ",", ".");   // (2-> casas decimais) - ("," -> separador decimal) - ("." -> separador de milhar)
}

?>