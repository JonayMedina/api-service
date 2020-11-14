<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

/**
* @OA\Info(title="API Usuarios", version="1.0")
*
* @OA\Server(url="http://localhost:8000")
*/
class SearchController extends Controller
{
	/**
    * @OA\Get(
    *	path="/api/search/{search}",
    *	summary="Donde 'allen' es el nombre a realizar la Consulta en las apis de Itunes, tvmaze y crcind.com",
	*	description="Obtiene la lista de coincidencias",
	*	operationId="search",
	*	tags={"Busqueda de persona por Nombre"},
	*	security={ {"bearer": {} }},
	*	@OA\Parameter(
	*    description="nombre de la persona o cantante",
	*    in="path",
	*    name="search",
	*    required=true,
	*    example="allen",
	*    @OA\Schema(
	*       type="string"
	*    )
	*   ),
    *     @OA\Response(
    *         response=200,
    *         description="Muestra resultados de las paginas."
    *     ),
    *     @OA\Response(
    *         response="default",
    *         description="Ha ocurrido un error."
    *     )
    * )
    */
    public function search($search)
    {
    	$values = [];
    	$itunes_response = $this->itunes($search);
    	$tvmaze_res = $this->tvmaze($search);
    	$crcind_res = $this->crcind($search);
    	array_push($values, ['itunes' => $itunes_response]);
    	array_push($values,['tvmaze' => $tvmaze_res]);
    	array_push($values,['crcind_res' => $crcind_res]);

    	return response($values);
    }

    protected function itunes($param)
    {
    	$itunes = Http::get('https://itunes.apple.com/search?term='.$param)->throw()->json();
    	return $itunes;
    }

    protected function tvmaze($param)
    {
    	$response = Http::get('http://api.tvmaze.com/search/shows?q='.$param)->throw()->json();

    	return $response;
    }

    protected function crcind($param)
    {
    	$data = '';
    	$site = 'http://www.crcind.com';
    	$wsdl = 'http://www.crcind.com/csp/samples/SOAP.Demo.cls?WSDL';

    	$person = new \SOAPClient($wsdl);

    	$person_data= $person->GetListByName(['name' => $param]);
    	foreach ($person_data as $p) {
    		if ($p->PersonIdentification) {
				$data = $p->PersonIdentification;
				$data->site = $site;
    		}
    	}
    	return $data;
    }

}
