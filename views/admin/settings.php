<div id="poststuff" class="wrap">
	<h2>Forex Quotes</h2>
	<div class="postbox">
		<h3><?php _e('Options', 'fxticker-lang') ?></h3>
		<div class="inside">
			<form action="options-general.php?page=forex-ticker" method="post">
				<table class="form-table">
					<tr>
						<td colspan="2" style="border-top: 1px #ddd solid; background: #eee"><strong><?php _e('Use', 'fxticker-lang'); ?></strong></td>
					</tr>
					<tr>
						<th></th>
						<td>To insert the ticker on the page simply insert the following shortcode in the text editor: <b>[forex-quotes type="ticker"]</b> or <b>[forex-quotes type="table"]</b>.<br/>Alternatively, you can use it in the widget section.</td>
					</tr>
					<tr>
						<td colspan="2" style="border-top: 1px #ddd solid; background: #eee"><strong><?php _e('Ticker', 'fxticker-lang'); ?></strong></td>
					</tr>
					<tr>
						<th><?php _e("Source", "fxticker-lang"); ?></th>
						<td>
							<select name="fxticker_source">
								<?php foreach ($this->fxTickerSources as $k => $v) { ?>
								<option <?php echo (($this->opt["fxticker_source"] == $k) ? "selected" : ""); ?> value="<?php echo $k; ?>"><?php echo $k; ?></option>
								<?php } ?>
							</select>
						</td>
					</tr>
					<tr>
						<th><?php _e("IP", "fxticker-lang"); ?></th>
						<td>
							<input name="fxticker_ip" type="text" size="70" value="<?php echo $this->opt["fxticker_ip"]; ?>" />
							<br />
							<?php _e("e.g., 1.1.1.1", "fxticker-lang"); ?>
						</td>
					</tr>
					<tr>
						<th><?php _e("Cache Expiration Time", "fxticker-lang"); ?></th>
						<td>
							<input name="fxticker_expiration" type="text" size="70" value="<?php echo $this->opt["fxticker_expiration"]; ?>" />
							<br />
							<?php _e("in seconds. e.g., 5  (min. 5 seconds)", "fxticker-lang"); ?>
						</td>
					</tr>
					<tr>
						<th><?php _e("Flags", "fxticker-lang"); ?></th>
						<td>
							<input name="fxticker_flags" value="1" type="checkbox" <?php echo ((isset($this->opt["fxticker_flags"])) ? "checked='checked'" : ""); ?> />
							<br />
							<?php _e("use flag images for the pairs", "fxticker-lang"); ?>
						</td>
					</tr>
					<tr>
						<th><?php _e("Pairs", "fxticker-lang"); ?></th>
						<td>
							<?php foreach ($this->fxTickerPairs as $k => $v) { ?>
							<label>
								<input name="fxticker_pairs[<?php echo $v; ?>]" value="<?php echo $v; ?>" type="checkbox" <?php echo ((isset($this->opt["fxticker_pairs"][$v])) ? "checked='checked'" : ""); ?> /> <?php echo $v; ?>
							</label>
							<br/>
							<?php } ?>
						</td>
					</tr>
					<tr>
						<th><?php _e("Main Color", "fxticker-lang"); ?></th>
						<td>
							<input class="ticker-admin-color" name="fxticker_header_color" type="text" size="70" value="<?php echo $this->opt["fxticker_header_color"]; ?>" />
						</td>
					</tr>
					<tr>
						<th><?php _e("Second Color", "fxticker-lang"); ?></th>
						<td>
							<input class="ticker-admin-color" name="fxticker_header_color_2" type="text" size="70" value="<?php echo $this->opt["fxticker_header_color_2"]; ?>" />
						</td>
					</tr>
				</table>
				<p class="submit">
					<input name="fxticker_save" class="button-primary" value="<?php _e('Save Changes'); ?>" type="submit" />
				</p>
			</form>
		</div>
	</div>
</div>