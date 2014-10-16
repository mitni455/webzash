<?php
/**
 * The MIT License (MIT)
 *
 * Webzash - Easy to use web based double entry accounting software
 *
 * Copyright (c) 2014 Prashant Shah <pshah.mumbai@gmail.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

function account_st_short($account, $c = 0, $THIS, $dc_type)
{
	$counter = $c;
	if ($account->id != 0)
	{
		if ($dc_type == 'D' && $account->cl_total_dc == 'C' && calculate($account->cl_total, 0, '!=')) {
			echo '<tr class="tr-group dc-error">';
		} else if ($dc_type == 'C' && $account->cl_total_dc == 'D' && calculate($account->cl_total, 0, '!=')) {
			echo '<tr class="tr-group dc-error">';
		} else {
			echo '<tr class="tr-group">';
		}

		echo '<td class="td-group">';
		echo print_space($counter);
		echo "&nbsp;" . h($account->name);
		echo '</td>';

		echo '<td class="text-right">';
		echo toCurrency($account->cl_total_dc, $account->cl_total);
		echo print_space($counter);
		echo '</td>';

		echo '</tr>';
	}
	foreach ($account->children_groups as $id => $data)
	{
		$counter++;
		account_st_short($data, $counter, $THIS, $dc_type);
		$counter--;
	}
	if (count($account->children_ledgers) > 0)
	{
		$counter++;
		foreach ($account->children_ledgers as $id => $data)
		{
			if ($dc_type == 'D' && $data['cl_total_dc'] == 'C' && calculate($data['cl_total'], 0, '!=')) {
				echo '<tr class="tr-ledger dc-error">';
			} else if ($dc_type == 'C' && $data['cl_total_dc'] == 'D' && calculate($data['cl_total'], 0, '!=')) {
				echo '<tr class="tr-ledger dc-error">';
			} else {
				echo '<tr class="tr-ledger">';
			}

			echo '<td class="td-ledger">';
			echo print_space($counter);
			echo $THIS->Html->link($data['name'], array('plugin' => 'webzash', 'controller' => 'reports', 'action' => 'ledgerstatement', 'ledgerid' => $data['id']));
			echo '</td>';

			echo '<td class="text-right">';
			echo toCurrency($data['cl_total_dc'], $data['cl_total']);
			echo print_space($counter);
			echo '</td>';

			echo '</tr>';
		}
	$counter--;
	}
}

function print_space($count)
{
	$html = '';
	for ($i = 1; $i <= $count; $i++) {
		$html .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
	}
	return $html;
}

?>

<script type="text/javascript">
$(document).ready(function() {
	$('.show-tooltip').tooltip({trigger: 'manual'}).tooltip('show');
});
</script>

<?php
/* Show difference in opening balance */
if ($bsheet['is_opdiff']) {
	echo '<div><div role="alert" class="alert alert-danger">' .
		__d('webzash', 'There is a difference in opening balance of ') .
		toCurrency($bsheet['opdiff']['opdiff_balance_dc'], $bsheet['opdiff']['opdiff_balance']) .
		'</div></div>';
}

/* Show difference in liabilities and assets total */
if (calculate($bsheet['final_liabilities_total'], $bsheet['final_assets_total'], '!=')) {
	$final_total_diff = calculate($bsheet['final_liabilities_total'], $bsheet['final_assets_total'], '-');
	echo '<div><div role="alert" class="alert alert-danger">' .
		__d('webzash', 'There is a difference in Total Liabilities and Total Assets of ') .
		toCurrency('X', $final_total_diff) .
		'</div></div>';
}
?>

<table>

	<!-- Liabilities and Assets -->
	<tr>
		<td class="table-top width-50">
			<table class="stripped">
				<tr>
					<th><?php echo __d('webzash', 'Liabilities and Owners Equity'); ?></th>
					<th class="text-right"><?php echo __d('webzash', '(Cr) Amount'); ?></th>
				</tr>
				<?php
					foreach ($bsheet['liabilities_list'] as $row => $group) {
						echo account_st_short($group, $c = 0, $this, 'C');
					}
				?>
			</table>
		</td>

		<td class="table-top width-50">
			<table class="stripped">
				<tr>
					<th><?php echo __d('webzash', 'Assets'); ?></th>
					<th class="text-right"><?php echo __d('webzash', '(Dr) Amount)'); ?></th>
				</tr>
				<?php
					foreach ($bsheet['assets_list'] as $row => $group) {
						echo account_st_short($group, $c = 0, $this, 'D');
					}
				?>
			</table>
		</td>
	</tr>

	<tr>
		<td class="table-top width-50">
			<div class="report-tb-pad"></div>
			<table class="stripped">
				<?php /* Liabilities Total */ ?>
				<?php if (calculate($bsheet['liabilities_total'], 0, '>=')) {
					echo '<tr>';
					echo '<td>' . __d('webzash', 'Total Liability and Owners Equity') . '</td>';
					echo '<td class="text-right">' . toCurrency('C', $bsheet['liabilities_total']) . '</td>';
					echo '</tr>';
				} else {
					echo '<tr class="dc-error">';
					echo '<td>' . __d('webzash', 'Total Liability and Owners Equity') . '</td>';
					echo '<td class="text-right show-tooltip" data-toggle="tooltip" data-original-title="Expecting positive Cr balance">' . toCurrency('C', $bsheet['liabilities_total']) . '</td>';
					echo '</tr>';
				}
				?>
				<tr>
					<?php
					/* Net profit */
					if (calculate($bsheet['pandl'], 0, '>=')) {
						echo '<td>' . __d('webzash', 'Profit & Loss Account (Net Profit)') . '</td>';
						echo '<td class="text-right">' . toCurrency('', $bsheet['pandl']) . '</td>';
					} else {
						echo '<td>&nbsp</td>';
						echo '<td>&nbsp</td>';
					}
					?>
				</tr>
				<?php
				if ($bsheet['is_opdiff']) {
					echo '<tr class="error-text">';
					/* If diff in opening balance is Dr */
					if ($bsheet['opdiff']['opdiff_balance_dc'] == 'D') {
						echo '<td>' . __d('webzash', 'Diff in O/P Balance') . '</td>';
						echo '<td class="text-right">' . toCurrency('D', $bsheet['opdiff']['opdiff_balance']) . '</td>';
					} else {
						echo '<td>&nbsp</td>';
						echo '<td>&nbsp</td>';
					}
					echo '</tr>';
				}
				?>

				<?php
				/* Total */
				if (calculate($bsheet['final_liabilities_total'],
					$bsheet['final_assets_total'], '==')) {
					echo '<tr class="bold-text">';
				} else {
					echo '<tr class="bold-text error-text">';
				}
					echo '<td>' . __d('webzash', 'Total') . '</td>';
					echo '<td class="text-right">' .
						toCurrency('', $bsheet['final_liabilities_total']) .
						'</td>';
				echo '</tr>';
				?>
			</table>
		</td>

		<td class="table-top width-50">
			<div class="report-tb-pad"></div>
			<table class="stripped">
				<?php /* Assets Total */ ?>
				<?php if (calculate($bsheet['assets_total'], 0, '>=')) {
					echo '<tr>';
					echo '<td>' . __d('webzash', 'Total Assets') . '</td>';
					echo '<td class="text-right">' . toCurrency('D', $bsheet['assets_total']) . '</td>';
					echo '</tr>';
				} else {
					echo '<tr class="dc-error">';
					echo '<td>' . __d('webzash', 'Total Assets') . '</td>';
					echo '<td class="text-right show-tooltip" data-toggle="tooltip" data-original-title="Expecting positive Dr Balance">' . toCurrency('D', $bsheet['assets_total']) . '</td>';
					echo '</tr>';
				}
				?>
				<tr>
					<?php
					/* Net loss */
					if (calculate($bsheet['pandl'], 0, '>=')) {
						echo '<td>&nbsp</td>';
						echo '<td>&nbsp</td>';
					} else {
						echo '<td>' . __d('webzash', 'Profit & Loss Account (Net Loss)') . '</td>';
						$positive_pandl = calculate($bsheet['pandl'], 0, 'n');
						echo '<td class="text-right">' . toCurrency('', $positive_pandl) . '</td>';
					}
					?>
				</tr>
				<?php
				if ($bsheet['is_opdiff']) {
					echo '<tr class="error-text">';
					/* If diff in opening balance is Cr */
					if ($bsheet['opdiff']['opdiff_balance_dc'] == 'C') {
						echo '<td>' . __d('webzash', 'Diff in O/P Balance') . '</td>';
						echo '<td class="text-right">' . toCurrency('C', $bsheet['opdiff']['opdiff_balance']) . '</td>';
					} else {
						echo '<td>&nbsp</td>';
						echo '<td>&nbsp</td>';
					}
					echo '</tr>';
				}
				?>

				<?php
				/* Total */
				if (calculate($bsheet['final_liabilities_total'],
					$bsheet['final_assets_total'], '==')) {
					echo '<tr class="bold-text">';
				} else {
					echo '<tr class="bold-text error-text">';
				}
					echo '<td>' . __d('webzash', 'Total') . '</td>';
					echo '<td class="text-right">' .
						toCurrency('', $bsheet['final_assets_total']) .
						'</td>';
				echo '</tr>';
				?>
			</table>
		</td>
	</tr>
	<!-- END Liabilities and Assets -->

</table>
