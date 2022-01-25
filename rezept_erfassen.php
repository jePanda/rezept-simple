<?php
echo '
<h1>Formular</h1>
<p>Erstellt mit PHP</p>
';

if(isset($_POST['saverez'])) {

    $rezept = $_POST['rezept'];
    $anz = $_POST['anzahl'];

    $arrayvalue = array($rezept);
    $query = "insert into rezeptname (rez_name) values(?)";
    $rezeptname = stmtcheck($con, $query, $arrayvalue);
    // Funktion stmtcheck zum Ausführen von SQL-Statements - siehe am Ende des Source-Codes ... nach ELSE
    /*
        Es wird versucht, den Rezeptnamen einzufügen
        falls der Rezeptname bereits vorhanden ist, wird
        null zurückgegeben
    */

    if($rezeptname == null) { // Rezeptname bereits vorhanden
        $query1 = "select rez_id from rezeptname where rez_name like ?";
        $rezeptname = stmtcheck($con, $query1, $arrayvalue);
        $rezidfetch = $rezeptname->fetch(PDO::FETCH_NUM);
        $rezid = $rezidfetch[0];
    } else
    {
        $rezid = $con->lastInsertId(); // neuer Rezeptname, neue ID
    }

    /* Zutaten "auslesen */
    $query2 = "select zut_id, zut_name, ein_name from zutat_einheit natural join (zutat, einheit) order by zut_name, ein_name";
    $zut = stmtcheck($con, $query2);

    echo '<form method="post">';
    for($i = 0; $i < $anz; $i++) { // for-Schleife: da es mehrere Zutaten geben kann
        echo '<input type="number" name="menge[]">'; // Ein Array für die Menge
        echo '<select name="zueid[]'.$i.'">'; // Ein Array für die Zutaten_Einheit ID
            while($row = $zut->fetch(PDO::FETCH_NUM)) // Alle Zutanten mit Einheit in einen Drop-Down ausgeben
            {
                echo '<option value="'. $row[0].'">'.$row[1].' '.$row[2].'</option>';
            }
        echo '</select><br>';
        $zut = stmtcheck($con, $query2);
    }
    echo '<label>Zubereitung:</label><textarea name="zubereitung"></textarea>';
    echo '<input type="hidden" name="rezid" value="'.$rezid.'">'; // RezeptID als verstecktes Element weiterleiten
    echo '<input type="hidden" name="anz" value="'.$anz.'">'; // Anzahl als verstecktes Element weiterleiten
    echo '<input type="submit" name="saverezept" value="speichern">';
    echo '</form>';
} else if(isset($_POST['saverezept']))
{
    $rezid = $_POST['rezid'];
    $anz = $_POST['anz'];
    $zubereitung = $_POST['zubereitung'];
    $menge = $_POST['menge']; // Array
    $zueid = $_POST['zueid']; // Array

    $query3 = "insert into zubereitung (zub_beschreibung, rez_id) values(?, ?)";
    $arrayvalue = array($zubereitung, $rezid);
    $zub = stmtcheck($con, $query3, $arrayvalue);
    $zubid = $con->lastInsertId();

    echo 'Folgendes wird erfasst:<br>';
    echo "RezeptID (falls noch nicht vorhanden) $rezid - zub_id $zubid<br>";

    /* Zutat_Einheit + Menge erfassen */
    $mcount = 0; // Zähler für Menge
    foreach($zueid as $zeid)
    {
        $query4 = "insert into zubereitung_einheit values (?, ?, ?)";
        $arrayvalue = array($zubid, $zeid, $menge[$mcount]);
        $zubereitungeinheit = stmtcheck($con, $query4, $arrayvalue);
        $mcount++;
    }
}

else {

?>
<form method="post">
    <label for="rez">Rezeptname:</label><input type="text" name="rezept" id="rez" placeholder="z.B. Marmorkuchen"><br>
    <label for="anz">Anzahl der Zutaten:</label><input type="number" name="anzahl" id="anz"><br>
    <input type="submit" name="saverez" value="speichern">
</form>
<?php
}

function stmtcheck($con, $query, $arrayvalue = null)
{
    $stmt = $con->prepare($query);
    try
    {
        if($arrayvalue != null)
        {
            $stmt->execute($arrayvalue); // to do
        } else
        {
            $stmt->execute();
        }
        return $stmt;

    } catch (Exception $e)
    {
        switch($e->getCode())
        {
            case 23000:
                // nothing to do, Eintrag in DB ist bereits vorhanden (Unique)
                break;
            default:
                echo '<b>'.$e->getCode().': '.$e->getMessage().'<b><br>';
        }
    }
}

