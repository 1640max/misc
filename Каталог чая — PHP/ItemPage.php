<?php

function EchoAString($Name, $Value, $Smaller = false) // Выводит в полосатую табличку одну строку информации
{
  // Если в Value пусто, то строка не пишется
	if ($Value != "")
	{
		echo '<div class="StripedList"';
		if ($Smaller)
			echo ' style="font-size: 95%"';
		echo '><b>'.$Name.': </b>'.$Value.'</div>';
	}
}

if ($_GET == NULL) // Без параметров на странице делать нечего
{
	echo "Error";
	return;
}

require_once 'connect.php';

$Rus = array(
		"Green"	=> "Зелёный чай",
		"Black" => "Чёрный чай",
		"Red" => "Красный чай (каркаде)",
		
		"Paket" => "Пакетированый",
		"List" => "Листовой",
		
		"Pachka" => "Картонная пачка",
		"Jest" => "Жестяная банка (ж/б)",
		"MY" => "Мягкая (МУ)"
		);

if ($_GET["ProdType"] == "Tea") // Типа от инъекций защита, я на безопасника учусь вообще-то
{
	$PT = "Tea";
}
elseif ($_GET["ProdType"] == "ChocoCocoa" || $_GET["ProdType"] == "Coffee"|| $_GET["ProdType"] == "NoTea")
{
	$PT = "NoTea";
}
$id = (int)$_GET["ID"]; // Тоже защита. Можешь сделать лучше? Сделай, я просто не умею по-другому :D
$query = "SELECT * FROM ".$PT." WHERE ID = ".$id;
$result = mysqli_query($link, $query);
$Item = mysqli_fetch_assoc($result);

echo '@'.$Item["Name"].'@'; // В заголовок. Собачка - разделитель для split.
echo $Item["Net"].'@'; // Для автоматического написания сообщения в онлайн заявке
echo '<div id="UpperHalf" style="display: table"><div style="float: left; max-width: 35%; margin-right: 5px; display: table-cell">';
if ($Item['PhotoURL'] == "")
{
	echo '<img src="userfiles/decorations/nophoto.gif" width = 100%>';
}
else
{
	echo '<a class="zoom" href="'.$Item["PhotoURL"].'">
	<img src="userfiles/MiniCopies'.substr($Item['PhotoURL'], 9).'" width=100%></a>';
}

echo '</div><div style="display:table">'; // Закрывающийся див - это див, в котором картинка
EchoAString('Масса нетто', $Item["Net"]);
EchoAString('В коробке шт', $Item["InABox"]);
if ($PT == "Tea")
{
	EchoAString('Цвет', $Rus[$Item["TeaColour"]]);
	EchoAString('Тип', $Rus[$Item["TeaType"]]);
}
EchoAString('Упаковка', $Rus[$Item["PackType"]]);
EchoAString('Описание', $Item["Description"], true);
EchoAString('Соответствует', $Item["Standard"], true);
EchoAString('Штрихкод', $Item["Barcode"], true);
echo "</div></div>";

$query = 'SELECT COUNT(*) FROM '.$PT; // Запрашиваю количество элементов отдельно. Да, медленно, но по-другому не знаю как. Да и не критично.
$result = mysqli_query($link, $query);
$row = mysqli_fetch_row($result);
$Amount = $row[0];

// Получаем 4 ближайших строки. С обеих сторон по две, но если данная строка стоит с краю, то всё равно надо 4 строки.
$minID = $Item["ID"] - 2;
$maxID = $Item["ID"] + 2;
if ($Item["ID"] <= 2)
{
	$minID = 1;
	$maxID = 5;
}
elseif ($Amount - $Item["ID"] < 2)
{
	$minID = $Amount - 4;
	$maxID = $Item["ID"];
}
$query = "SELECT ID, PhotoURL, Name FROM ".$PT." WHERE ID BETWEEN ".$minID." AND ".$maxID." AND ID != ".$Item["ID"];
$result = mysqli_query($link, $query);

$PhotosHTML = "";
$NamesHTML = "";
while ($SimilarItem = mysqli_fetch_assoc($result))
{
	$PhotosHTML .= '<td style="width: 25%">';
	if ($SimilarItem['PhotoURL'] == "")
		{
			$PhotosHTML .= '<img src="userfiles/decorations/nophoto.gif" width = 100%>';
		}
	else
		{
			$PhotosHTML .= '<a class="zoom" href='.$SimilarItem['PhotoURL'].'><img src="userfiles/MiniCopies'.substr($SimilarItem['PhotoURL'], 9).'" style="padding: 0; max-height: 150px; max-width: 100%"></a>
			</td>';
		}
	$NamesHTML .= '<th style="font-size: 10px; padding-left: 3px; padding-right: 3px">
			<a style="color: #000000" href="tovar.html?ProdType='.$PT.'&ID='.$SimilarItem['ID'].'">'.$SimilarItem['Name'].'</a>
			</th>';
}

// Выводим код таблички с похожими товарами
echo '<div class="CatForm"><span style="font-size: 15px; font-weight:bold">Похожие товары:</span>
<table bgcolor="#ffffff" style="width: 100%; height: 100%">
		<tr style="height: 80%">';
echo $PhotosHTML;
echo '</tr>
		<tr style="background: #eeeeee; color: #000000;">';
echo $NamesHTML;
echo '</tr>
</table></div>';
?>