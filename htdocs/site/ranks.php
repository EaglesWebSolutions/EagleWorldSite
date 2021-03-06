<?php 
include ("include.inc.php");
$ptitle="Highscores - $cfg[server_name]";
include ("header.inc.php");

$SQL = new SQL();
?>
<center>
<div id="content">
<div class="top">Highscores</div>
<div class="mid">
<select name="sort" onchange="self.location.href=this.value">
<?php 
if (empty($_GET['sort'])) $_GET['sort'] = 'level';

$options = array_merge(array('level', 'maglevel'), $cfg['skill_names']);

foreach ($options as $skill){
	if ($skill == $_GET['sort'])
		$selected = ' selected="selected"';
	else
		$selected = '';
	echo '<option value="ranks.php?sort='.$skill.'"'.$selected.'>'.ucfirst($skill).'</option>';
}
echo '</select><br><br>';

if (!isset($_GET['page']) || $_GET['page'] < 0) $p = 0;
else $p = (int) $_GET['page'];

if ($_GET['sort'] == 'level' || $_GET['sort'] == 'maglevel'){
	$query = 'SELECT players.vocation, groups.access, groups.id, players.name, players.level, players.maglevel, players.experience FROM players LEFT OUTER JOIN groups ON players.group_id = groups.id ORDER BY `'.mysql_real_escape_string($_GET['sort']).'` DESC LIMIT '.$cfg['ranks_per_page']*$p.', '.$cfg['ranks_per_page'].';';
	$key = $_GET['sort'];
}elseif (in_array($_GET['sort'],$cfg['skill_names'])){
	$query = 'SELECT groups.access, a1.* FROM (SELECT players.group_id, players.name, player_skills.value FROM players, player_skills WHERE players.id = player_skills.player_id AND player_skills.skillid = '.array_search($_GET['sort'], $cfg['skill_names']) .') AS a1 LEFT OUTER JOIN groups ON a1.group_id = groups.id ORDER BY `value` DESC LIMIT '.$cfg['ranks_per_page']*$p.', '.$cfg['ranks_per_page'].';';
	$key = 'value';
}elseif ($_GET['sort'] == 'census'){
	$SQL->myQuery('SELECT players.sex, COUNT(players.id) as number FROM `players` GROUP BY players.sex');
	$total = 0;
	while ($a = $SQL->fetch_array()){
		$genders[$a['sex']] = $a['number'];
		$total += $a['number'];
	}
	$gender_names = array(0 => 'Female',1 => 'Male');
	echo '<p><h2>Gender</h2>';
	echo '<table style="font-weight: bold">';
	foreach (array_keys($genders) as $gender)
		echo '<tr><td>'.$gender_names[$gender].'</td><td>'.percent_bar($genders[$gender],$total).'</td><td>('.$genders[$gender].')</td></tr>';
	echo '</table></p>';
	$SQL->myQuery('SELECT players.vocation, COUNT(players.id) as number FROM `players` GROUP BY players.vocation');
	$total = 0;
	while ($a = $SQL->fetch_array()){
		$vocations[$a['vocation']] = $a['number'];
		$total += $a['number'];
	}
	echo '<p><h2>Vocations</h2>';
	echo '<table style="font-weight: bold">';
	foreach (array_keys($vocations) as $vocation)
		echo '<tr><td>'.$cfg['vocations'][$vocation]['name'].'</td><td>'.percent_bar($vocations[$vocation],$total).'</td><td>('.$vocations[$vocation].')</td></tr>';
	echo '</table></p>';	

}else{$error = "Invalid sort argument";}

if (isset($query)){
?>
<table><tr class="color0"><td style="width:30px">#</td><td style="width:200px"><b>Name</b></td><td style="width:130px"><b>Vocation</b></td><td style="width:40px"><b><?php echo htmlspecialchars(ucfirst($_GET['sort']))?></b></td></tr>
<?php
	$SQL->myQuery($query);
	if ($SQL->failed())
		throw new Exception('SQL query failed:<br/>'.$SQL->getError());
	else{
		$i = $cfg['ranks_per_page']*$p;
		while($a = $SQL->fetch_array())
		if ($a['access'] < $cfg['ranks_access'])
			{
				$i++;
				echo '<tr '.getStyle($i).'><td>'.$i.'</td><td><a href="characters.php?player_name='.urlencode($a['name']).'">'.htmlspecialchars($a['name']).'</a>';
				if ($a['vocation'] >= 15){ echo "<a href=vantagens.php target=vantagens><font color=yellow><b> (VIP)</b></font></a></td>"; } else { echo '</td>'; }
				echo '<td>'.$cfg['vocations'][$a['vocation']]['name'].'</td><td>'.$a[$key].'</td></tr>'."\n";
			}
	}
}
?>
</table>
</div>
<div class="bot"></div>
</div>
<?php include('footer.inc.php');?>