<?php 
/*Настройки*/ // 
require_once 'request.php';	
header('Access-Control-Allow-Origin: '.$_SERVER['HTTP_ORIGIN']);
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
$data = $_POST;
$id = $data['id'];
$api 	= $data['api'];
// $id = ['9614391','9614767', '9677883', '9677857'];
// $api = [
// 	"amouser" => 'kent62@inbox.ru',
// 	"amohash" => 'ffffe1eed5810c85aabb1a7f11a0ff24a16a650d',
// 	"subdomain" => 'vasso'
// ];

if (isset($id) && isset($api)) {
	$user = $api['subdomain'];
	$file = "leads.{$user}.csv"; 	#имя файла
	
	$connect = new Connect($api); #соединение с API
	/*Запрос сделок*/
	for($i=0;$i<count($id_l); $i++) {
		$res .= "id%5B%5D=".$id_l[$i]."&";
	}
	$res = substr($res, 0, -1);
	$path_lead  = 'leads?'.$res;
	$leads 		= $connect->get($path_lead);
	$leads 		= $leads['_embedded']['items'];
	/*Первичный результирующий массив*/
	foreach ($leads as $key => $value) {
		
		$leads_info[$key]['name'] 			=	$value['name'];
		$leads_info[$key]['created_at'] 	=	date("d, m, Y",$value['created_at']);
		$leads_info[$key]['contacts_name']	=	$value['contacts']['id'];
		$leads_info[$key]['company_name']	=	$value['company']['id'];
		$leads_info[$key]['custom_fields']	=	$value['custom_fields'];
		$leads_info[$key]['tags'] 			=	$value['tags'];	
		
		foreach ($leads_info[$key]['tags'] as $key_t => $value_t) {
			$leads_info[$key]['tags'][$key_t] = $value_t['name'];
		}
		
	}
		$titles = array_keys($leads_info[$key]); #первичные заголовки
	/*Обновление контактов*/
		/*сбор ид всех контактов*/
	foreach ($leads_info as $key => $value) {
		$contacts_id[$key] = $value['contacts_name'];
	}
		/*путь запроса*/
	foreach ($contacts_id as $ids) {
		foreach ($ids as $i => $id) {
			$res1 .= "id%5B%5D=".$id."&";
		}
	}
	$res1 = substr($res1, 0, -1);
	$path_cont = 'contacts?'.$res1;
		/*запрос к апи*/
	$contacts = $connect->get($path_cont);
	$contacts = $contacts['_embedded']['items'];
		/*формируем массив ид=имя*/
	foreach($contacts as $key_c => $value_c) {
		$contacts_names[$value_c['id']] = $value_c['name'];
	}
		/*заменяем в рез массиве ид на имя*/
	foreach($leads_info as $key =>$val) {
		$leads_info[$key]['contacts_name'] = array_flip($leads_info[$key]['contacts_name']); 
		foreach ($contacts_names as $id => $name) {
			if (isset($leads_info[$key]['contacts_name'][$id])) {
				$leads_info[$key]['contacts_name'][$id] = $name;
			}
		}		 	
	}

		/*Обновление компаний*/	
	foreach ($leads_info as $key => $value) {
		$id_c1ompanies[$key] = $value['company_name'];
	}
	foreach ($id_companies as $key => $value) {
	$res2 .= "id[{$key}]=".$value."&";
	}
	$res2 = substr($res2, 0, -1);
	$path_company = 'companies?'.$res2;
	
	$company = $connect->get($path_company);
	$company = $company['_embedded']['items'];
	foreach ($company as $key => $value) {
		$leads_info[$key]['company_name'] = $value['name'];
	}



		/*Кастомные поля*/
	foreach ($leads_info as $key => $value) {
		foreach ($leads_info[$key]['custom_fields'] as $field => $info) {		
			$leads_info[$key]['custom_fields'][$field] = $info['name'];
			$custom_t[$field] = $info['name']." id=".$info['id']; #добавляем в заголовки названия кастомных полей
			foreach ($info['values'] as $key_f => $value_f) {							
				$leads_info[$key][$info['name']." id=".$info['id']][$key_f] = $value_f['value'];
				if (is_array($leads_info[$key][$info['name']." id=".$info['id']])) {
			foreach ($leads_info[$key][$info['name']." id=".$info['id']][$key_f] as $k => $v) {
				array_splice($leads_info[$key][$info['name']." id=".$info['id']], 0, -6); 
				$leads_info[$key][$info['name']." id=".$info['id']][$k] ="{$k}: {$v}";
				
			}
		}
			}							
		$leads_info[$key][$info['name']." id=".$info['id']] = implode("\n", $leads_info[$key][$info['name']." id=".$info['id']]);

		}					
	}
	
		/*имплодим ячейки*/
	foreach ($leads_info as $key => $value){
    $leads_info[$key]['contacts_name'] 	= implode(", ", $value['contacts_name']);
    $leads_info[$key]['custom_fields'] 	= implode(", ", $value['custom_fields']);
    $leads_info[$key]['tags'] 			= implode(", ", $value['tags']);
	}
	
		/*формируем окончательно заголовки*/
		
	$titles = array_merge($titles, $custom_t);
	$titles = array_flip($titles);
	
	foreach ($titles as $key => $value) {
		$titles[$key] = "";
	}

				/*формирование CSV*/
	
	$put = fopen($file, 'w');	
	fputcsv($put, array_keys($titles), ";");
	$count = count($leads_info);
	
for ($i=0; $i < $count; $i++) { 
	$lead = array_shift($leads_info);
	$row = array_merge($titles, $lead);
	fputcsv($put, $row, ";");	
	}
	fclose($put);

echo $file;
		
}
else {
	echo "eror";	
}
