<div id="table-forex-wrap">
	<table class="table-forex" cellspacing="1" cellpadding="2" border="0" style="width:100%;">
		<tbody>
			<tr class="table-forex-header" style="background-color: <?php echo ((isset($this->opt["fxticker_header_color"])) ? $this->opt["fxticker_header_color"] : "#0088CC"); ?>;">
				<td colspan="2">Symbol</td>
				<td>Bid</td>
				<td>Ask</td>
				<td>Spread</td>
			</tr>
			<?php
			$count = 0;
			foreach ($q as $k => $v) { ?>
					<tr class="content" style="<?php echo (($count % 2) ? "background-color: ".((isset($this->opt["fxticker_header_color_2"])) ? $this->opt["fxticker_header_color_2"] : "#EAEBEE") : ""); ?>;">
						<td class="icon"><img src="<?php echo FXTICKER_ASSETS_DIR; ?>img/<?php echo $v->upDown; ?>.gif" width="13" height="13"></td>
						<td>
							<?php if (isset($this->opt["fxticker_flags"])) {
								switch ($v->pair) {
									case "XAUUSD":
										echo $v->pair;
										break;
									case "XAGUSD":
										echo $v->pair;
										break;
									default:
										echo '<img src="'.FXTICKER_ASSETS_DIR.'img/'.$v->pair.'.png" width="32" height="16" alt="'.$v->pair.'" />';
										break;
								}
							} else {
								echo $v->pair;
							} ?>
						</td>
						<td><?php echo $v->bid; ?></td>
						<td><?php echo $v->ask; ?></td>
						<td><?php echo $v->spread; ?></td>
					</tr>
			<?php ++$count; } ?>
		</tbody>
	</table>
</div>