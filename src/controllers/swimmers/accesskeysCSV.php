<?php
// output headers so that the file is downloaded rather than displayed
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=accessKeys.csv');

// create a file pointer connected to the output stream
$output = fopen('php://output', 'w');

// output the column headings
fputcsv($output, array('Forename', 'Surname', 'Squad' , 'ASA Number' , 'Access Key'));

// fetch the data
$swimmers = $db->query("SELECT members.MForename, members.MSurname, squads.SquadName, members.ASANumber, members.AccessKey FROM (members INNER JOIN squads ON members.SquadID = squads.SquadID) ORDER BY `members`.`MForename` , `members`.`MSurname` ASC");

// loop over the rows, outputting them
while ($row = $swimmers->fetch(PDO::FETCH_ASSOC) {
  fputcsv($output, $row);
}
?>
