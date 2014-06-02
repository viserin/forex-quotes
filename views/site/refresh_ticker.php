<?php
$count = 0;
foreach ($q as $k => $v) { ?>
	<table cellspacing="0" cellpadding="0">
		<tr class="header" style="background-color: <?php echo (($count % 2) ? ((isset($this->opt["fxticker_header_color"])) ? $this->opt["fxticker_header_color"] : "#F8F8F8") : ((isset($this->opt["fxticker_header_color_2"])) ? $this->opt["fxticker_header_color_2"] : "#EBEBEB")); ?>;">
			<td>&nbsp;</td>
			<td>Symbol</td>
			<td>Bid</td>
			<td>Ask</td>
			<td>Spread</td>
		</tr>
		<tr class="content">
			<td class="icon"><img src="<?php echo FXTICKER_ASSETS_DIR ?>img/<?php echo $v->upDown; ?>.gif" width="13" height="13"></td>
			<td>
				<?php switch ($v->pair) {
					case "XAUUSD":
						echo $v->pair;
						break;
					case "XAGUSD":
						echo $v->pair;
						break;
					default:
						echo '<img src="'.FXTICKER_ASSETS_DIR.'img/'.$v->pair.'.png" width="32" height="16" alt="'.$v->pair.'" />';
						break;
				} ?>
			</td>
			<td><?php echo $v->bid; ?></td>
			<td><?php echo $v->ask; ?></td>
			<td><?php echo $v->spread; ?></td>
		</tr>
	</table>
<?php ++$count; } ?>