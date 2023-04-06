<?php

function GetFromBDAndShowTables($BDlink, $query)
{
	$result = mysqli_query($BDlink, $query);

	if (mysqli_num_rows($result) == 0)
		echo "<br>По Вашему запросу ничего не нашлось. Попробуйте убрать несколько галочек.";

	while ($Item = mysqli_fetch_assoc($result))
	{
		echo 	'<div class="product-box">
					<div class="product-box-imageBox">
            <img class="product-box-image" onclick="location.href=\'/tovar/?ID='.$Item['ID'].'\'" src="/userfiles/MiniCopies'.substr($Item['PhotoURL'], 9).'">
					</div>
				
					<h3 class="product-box-name">
						<a href="/tovar/?ID='.$Item['ID'].'">'.
						htmlspecialchars($Item['Name']).'
						</a>
					</h3>
					
					<a href="/tovar/?ID='.$Item['ID'].'" class="product-box-more-button">Подробнее</a>
				</div>';
	}
}

require_once 'connect.php';
require_once 'substitute.php'; // Защита от инъекций

// Общее начало и конец для каждого запроса
$query = 'SELECT * FROM Catalog WHERE Hidden = 0';
$queryOrder =' ORDER BY FIELD(ProductionType, "ChocoCocoa", "Coffee", "Tea") DESC, FIELD(Holiday, "Prosto", "Vosmoe marta", "New Year") DESC, FIELD(ID, 110, 111) DESC, NEW DESC, SALE DESC, ID DESC';

if ($_POST['ProductionType'] == 'Tea') // У кофе и гор.шока нет классификации, по ним запросы проще
{	
	$Brands = $_POST["Brands"];
	$TeaTypes = $_POST["TeaTypes"];
	$PackTypes = $_POST["PackTypes"];
	$TeaColours = $_POST["TeaColours"];
	$Holidays = $_POST["Holidays"];
	
	array_walk($Brands, 'SQLFilter');  // см. substitute.php
	array_walk($TeaTypes, 'SQLFilter'); // Это самопальная защита от инъекций
	array_walk($PackTypes, 'SQLFilter');
	array_walk($TeaColours, 'SQLFilter');
	array_walk($Holidays, 'SQLFilter');

	// Если хотя бы один параметр указан, то пишем AND и продолжаем запрос
	if (!(empty($Brands) AND empty($TeaTypes) AND empty($PackTypes) AND empty($TeaColours) AND empty($Holidays)))
	{
		$query .= " AND";
	}

	$NeedOR = false;
	$NeedAND = false;

	if(!empty($Brands))
	{
		$query.=' ('; 
		foreach ($Brands as $Brand)
		{
			if ($NeedOR) $query .= ' OR';
			$query .= ' Brand = "'.$Brand.'"';
			$NeedOR = true;
		}
		$NeedAND = true;
		$NeedOR = false;
		$query.=')'; 
	}

	if(!empty($TeaTypes))
	{
		if ($NeedAND) $query .= " AND";
		$query.=' (';
		foreach ($TeaTypes as $TeaType)
		{
			if ($NeedOR) $query .= ' OR';
			$query .= ' TeaType = "'.$TeaType.'"';
			$NeedOR = true;
		}
		$NeedAND = true;
		$NeedOR = false;
		$query.=')'; 
	}

	if(!empty($PackTypes))
	{
		if ($NeedAND) $query .= " AND";
		$query.=' (';
		foreach ($PackTypes as $PackType)
		{
			if ($NeedOR) $query .= ' OR';
			$query .= ' PackType = "'.$PackType.'"';
			$NeedOR = true;
		}
		$NeedAND = true;
		$NeedOR = false;
		$query.=')'; 
	}

	if(!empty($TeaColours))
	{
		if ($NeedAND) $query .= " AND";
		$query.=' (';
		foreach ($TeaColours as $TeaColour)
		{
			if ($NeedOR) $query .= ' OR';
			$query .= ' TeaColour = "'.$TeaColour.'"';
			$NeedOR = true;
		}
		$NeedAND = true;
		$NeedOR = false;
		$query.=')'; 
	}

	if(!empty($Holidays))
	{
		if ($NeedAND) $query .= " AND";
		$query.=' (';
		if ($Holidays[0] == "Prosto" and count($Holidays) == 1)
				$query .= ' Holiday IS NOT NULL';
		else
		foreach ($Holidays as $Holiday)
		{
			if ($NeedOR) $query .= ' OR';
			$query .= ' Holiday = "'.$Holiday.'"';
			$NeedOR = true;
		}
		$NeedAND = true;
		$NeedOR = false;
		$query.=')'; 
	}

	GetFromBDAndShowTables($custom_DB_link, $query.$queryOrder);
}
elseif ($_POST['ProductionType'] == 'Coffee' or $_POST['ProductionType'] == 'ChocoCocoa') // Тоже как бы защита
{
	$query .= ' AND ProductionType = "'.$_POST['ProductionType'].'"';
	GetFromBDAndShowTables($custom_DB_link, $query.$queryOrder);
}
elseif ($_POST['ProductionType'] == '')
{
	GetFromBDAndShowTables($custom_DB_link, $query.$queryOrder);
}

?>