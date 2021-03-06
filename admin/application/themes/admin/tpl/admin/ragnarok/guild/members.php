<?php
/**
 * @var $positions array
 * @var $members \Aqua\Ragnarok\Character[]
 * @var $memberCount int
 * @var $paginator \Aqua\UI\Pagination
 * @var $page \Page\Admin\Ragnarok\Server
 */
?>
<table style="width: 100%">
	<tr><td style="vertical-align: top">
		<table class="ac-table">
			<tbody>
				<tr class="ac-table-header alt"><td colspan="7"><?php echo __('ragnarok', 'members') ?></td></tr>
				<tr class="ac-table-header">
					<td></td>
					<td><?php echo __('ragnarok', 'name') ?></td>
					<td><?php echo __('ragnarok', 'class') ?></td>
					<td><?php echo __('ragnarok', 'base-level') ?></td>
					<td><?php echo __('ragnarok', 'job-level') ?></td>
					<td><?php echo __('ragnarok', 'position') ?></td>
					<td><?php echo __('ragnarok', 'guild-exp') ?></td>
				</tr>
			<?php if(empty($members)) : ?>
				<tr><td colspan="7" class="ac-table-no-result"><?php echo __('application', 'no-search-results') ?></td></tr>
			<?php else : foreach($members as $char) : ?>
				<tr>
					<td><img src="<?php echo $char->head() ?>"></td>
					<td><?php echo htmlspecialchars($char->name) ?></td>
					<td><?php echo htmlspecialchars($char->job()) ?></td>
					<td><?php echo number_format($char->baseLevel) ?></td>
					<td><?php echo number_format($char->jobLevel) ?></td>
					<td><?php
						if(isset($positions[$char->data['guild_position']])) {
							echo htmlspecialchars($positions[$char->data['guild_position']]['name']);
						} else {
							echo '--';
						}
						?></td>
					<td><?php echo number_format($char->data['guild_exp']) ?></td>
				</tr>
			<?php endforeach; endif; ?>
			</tbody>
			<tfoot>
				<tr><td colspan="7"><?php echo $paginator->render() ?></td></tr>
			</tfoot>
		</table>
		<span class="ac-search-result"><?php echo __('application',
				'search-results-' . ($memberCount === 1 ? 's' : 'p'),
				number_format($memberCount)) ?></span>
	</td><td style="vertical-align: top; width: 25%">
		<table class="ac-table">
			<thead>
			<tr class="alt"><td colspan="7"><?php echo __('ragnarok', 'positions') ?></td></tr>
			<tr>
				<td><?php echo __('ragnarok', 'position') ?></td>
				<td><?php echo __('ragnarok', 'name') ?></td>
				<td><?php echo __('ragnarok', 'mode') ?></td>
				<td><?php echo __('ragnarok', 'exp-mode') ?></td>
			</tr>
			</thead>
			<tbody>
			<?php if(empty($positions)) : ?>
				<tr><td colspan="4" class="ac-table-no-result"><?php echo __('application', 'no-search-results') ?></td></tr>
			<?php else : foreach($positions as $pos => $posData) : ?>
				<tr>
					<td>#<?php echo $pos + 1?></td>
					<td><?php echo htmlspecialchars($posData['name']) ?></td>
					<td><?php
						$mode = array();
						if($posData['mode'] & 0x01) {
							$mode[]= __('ragnarok-position', 0x01);
						}
						if($posData['mode'] & 0x10) {
							$mode[]= __('ragnarok-position', 0x10);
						}
						$mode = implode(', ',  $mode);
						echo $mode ?: '--';
						?></td>
					<td><?php echo $posData['exp'] ?>%</td>
				</tr>
			<?php endforeach; endif; ?>
			</tbody>
			<tfoot><tr><td colspan="4"></td></tr></tfoot>
		</table>
	</td></tr>
</table>
