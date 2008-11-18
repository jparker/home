<?
require_once('DB.php');
require_once('Mail.php');
require_once('Mail/mime.php');

define('URL_REDIRECT', $_SERVER['PHP_SELF']);
define('URL_VIEW', 'http://urgetopunt.com/vost/view.php');
define('MESSAGE', <<<EOM
The receipts listed below have been entered into the billing
system. Any discrepancies discovered during entry have been
included below the list of receipts.
EOM
);
define('SQL_CONTRACT', <<<EOM
  SELECT
    to_char(billables.date, 'fmDD Mon YYYY') AS date,
    agents.name AS agent,
    clients.name AS client,
    commids.name AS commid,
    billables.billed AS collected
  FROM billables
    INNER JOIN agents ON agents.id = billables.agent
    INNER JOIN clients ON clients.id = billables.client
    INNER JOIN commids ON commids.id = billables.commid
  WHERE billables.id = ?
EOM
);
define('SQL_RECEIPT', <<<EOM
  SELECT
    to_char(receipts.date, 'fmDD Mon YYYY') AS date,
    sum(billables.billed) AS gross,
    receipts.amount AS net
  FROM billables INNER JOIN receipts
    ON receipts.id = billables.receipt
  WHERE receipts.id = ?
  GROUP BY receipts.date, receipts.amount
EOM
);

function dbcroak($dberror) {
    echo '<pre>', $dberror->getMessage(), '</pre>';
    echo '<pre>', $dberror->getDebugInfo(), '</pre>';
    die('Database operation failed.');
}

function receipts_table($mode='html') {
    $table = '';
    if ($mode == 'html') {
	$table .= '<table border="0" cellpadding="5" cellspacing="0">' . "\n";
	$table .= "<caption>Newly Entered Receipts</caption>\n";
	$table .= '<tr>';
	$table .= '<th>ID</th><th>Date</th><th>Gross</th><th>Net</th>';
	$table .= "</tr>\n";

	foreach ($_SESSION['receipts'] as $receipt) {
	    $table .= '<tr>';
	    $table .= sprintf(
		'<td><a href="%s?receipt=%d">%d</a></td>',
		URL_VIEW,
		$receipt[id],
		$receipt[id]);
	    $table .= '<td align="right">' . $receipt['date'] . '</td>';
	    $table .= sprintf('<td align="right">%.2f</td>', $receipt['gross']);
	    $table .= sprintf('<td align="right">%.2f</td>', $receipt['net']);
	    $table .= "</tr>\n";
	}

	$table .= "</table>\n";
    } else {
	$table .= "[ NEWLY ENTERED RECEIPTS ]\n";
	$table .= sprintf(
	    "%-3s\t%-11s\t%-8s\t%-8s\n",
	    'ID',
	    'Date',
	    'Gross',
	    'Net');

	foreach ($_SESSION['receipts'] as $receipt) {
	    $table .= sprintf(
		"%3d\t%11s\t%8.2f\t%8.2f\n",
		$receipt['id'],
		$receipt['date'],
		$receipt['gross'],
		$receipt['net']);
	}

	$table .= "\n";
    }
    return $table;
}

function contracts_table($mode='html') {
    $table = '';
    if ($mode == 'html') {
	$table .= '<table border="0" cellpadding="5" cellspacing="0">' . "\n";
	$table .= "<caption>Contract Discrepancies</caption>\n";
	$table .= '<tr>';
	$table .= '<th>ID</th>';
	$table .= '<th>Date</th>';
	$table .= '<th>Client</th>';
	$table .= '<th>Commercial ID</th>';
	$table .= '<th>Billed</th>';
	$table .= '<th>Collected</th>';
	$table .= "</tr>\n";

	foreach ($_SESSION['contracts'] as $contract) {
	    $table .= '<tr>';
	    $table .= sprintf(
		'<td><a href="%s?session=%d">%d</a></td>',
		URL_VIEW,
		$contract['id'],
		$contract['id']);
	    $table .= '<td align="right">' . $contract['date'] . '</td>';
	    $table .= '<td>' . htmlentities($contract['client']) . '</td>';
	    $table .= '<td>' . htmlentities($contract['commid']) . '</td>';
	    $table .= sprintf('<td align="right">%.2f</td>', $contract['billed']);
	    $table .= sprintf('<td align="right">%.2f</td>', $contract['collected']);
	    $table .= "</tr>\n";
	}
	
	$table .= "</table>\n";
    } else {
	$table .= "[ CONTRACT DISCREPANCIES ]\n";
	$table .= sprintf(
	    "%-5s\t%-11s\t%-15s\t%-15s\t%-8s\t%-8s\n",
	    'ID',
	    'Date',
	    'Client',
	    'Commercial ID',
	    'Billed',
	    'Collect');

	foreach ($_SESSION['contracts'] as $contract) {
	    $table .= sprintf(
		"%5d\t%11s\t%-15s\t%-15s\t%8.2f\t%8.2f\n",
		$contract['id'],
		$contract['date'],
		substr($contract['client'], 0, 15),
		substr($contract['commid'], 0, 15),
		$contract['billed'],
		$contract['collected']);
	}

	$table .= "\n";
    }
    return $table;
}

session_start();

if ($_GET['reset']) {
    $_SESSION = array();
    session_destroy();
    header('Location: ' . URL_REDIRECT);
}

if ($_GET['mail']) {
    // send mail
    if (count($_SESSION['receipts']) <= 0) {
	header('Location: ' . URL_REDIRECT);
	exit;
    }

    $text = MESSAGE . "\n\n";;
    $text .= receipts_table('txt');
    if (count($_SESSION['contracts']) > 0)
	$text .= contracts_table('txt');
    if ($_SESSION['note'])
	$text .= "\n$_SESSION[note]\n";

    $html = '<p>' . MESSAGE . '</p>';
    $html .= receipts_table();
    if (count($_SESSION['contracts']) > 0)
	$html .= contracts_table();
    if ($_SESSION['note'])
	$html .= "<p>$_SESSION[note]</p>\n";

    $headers = array(
	'From' => 'vost@urgetopunt.com',
	'Bcc' => 'jparker@urgetopunt.com',
	'Subject' => 'Receipt Additions for ' . strftime('%D', time()));
    $mime = new Mail_mime("\n");
    $mime->setTXTBody($text);
    $mime->setHTMLBody($html);
    $body = $mime->get();
    $headers = $mime->headers($headers);

    $mail =& Mail::factory('mail');
    #$mail->send('awooga@speakeasy.net', $headers, $body);
    $mail->send('howardfparker@gmail.com', $headers, $body);

    $_SESSION = array();
    session_destroy();
    header('Location: ' . URL_REDIRECT);
}

$dbh =& DB::connect('pgsql://hpvo@unix(/var/run/postgresql)vost');
if (DB::isError($dbh)) dbcroak($dbh);
$dbh->setFetchMode(DB_FETCHMODE_ASSOC);

if ($_GET['receipt']) {
    // add receipt to session data
    $row =& $dbh->getRow(SQL_RECEIPT, array($_GET['receipt']));
    if (DB::isError($row)) dbcroak($row);
    if ($row)
	$_SESSION['receipts'][] = array(
	    'id' => $_GET['receipt'],
	    'date' => $row['date'],
	    'gross' => $row['gross'],
	    'net' => $row['net']);
    header('Location: ' . URL_REDIRECT);
}

if ($_GET['contract']) {
    // add contract to session data
    $row =& $dbh->getRow(SQL_CONTRACT, array($_GET['contract']));
    if (DB::isError($row)) dbcroak($row);
    if ($row and $row['collected'] != $_GET['billed'])
	$_SESSION['contracts'][] = array(
	    'id' => $_GET['contract'],
	    'date' => $row['date'],
	    'client' => $row['client'],
	    'commid' => $row['commid'],
	    'billed' => $_GET['billed'],
	    'collected' => $row['collected']);
    header('Location: ' . URL_REDIRECT);
}

if ($_GET['note']) {
    // add note to session data
    $_SESSION['note'] = $_GET['note'];
}

$dbh->disconnect();
?>
<html>
<head>
<title>HPVO, Inc. Receipt Notifier</title>
</head>
<body>
<form method="get" action="<? echo $_SERVER['PHP_SELF']; ?>">
<table border="0" cellpadding="5" cellspacing="0">
<tr>
<th align="left">Contract</th>
<td><input type="text" name="contract" size="10" maxlength="5"/></td>
</tr>
<tr>
<th align="left">Billed</th>
<td><input type="text" name="billed" size="10" maxlength="8"/></td>
</tr>
<tr>
<th align="left">Receipt</th>
<td><input type="text" name="receipt" size="10" maxlength="4"/></td>
</tr>
<tr>
<td colspan="2">
<textarea name="note" cols="40" rows="8">
<? if ($_SESSION['note']) echo htmlentities($_SESSION['note']); ?>
</textarea>
</tr>
</tr>
<tr>
<td colspan="2">
<input type="submit" name="add" value="Add"/>
<input type="submit" name="reset" value="Reset"/>
<input type="submit" name="mail" value="Mail"/>
<input type="reset" value="Clear"/>
</td>
</tr>
</table>
</form>
<hr/>
<?
if (count($_SESSION['receipts']) > 0) {
    echo receipts_table();
}
if (count($_SESSION['contracts']) > 0) {
    echo contracts_table();
}
?>
</body>
</html>
