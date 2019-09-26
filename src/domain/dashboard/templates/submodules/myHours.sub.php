
<table id="" class="table table-bordered">
    <colgroup>
        <col class="con1" />
          <col class="con0"/>
    </colgroup>
    <thead>
        <tr>
            <th class='head1'><?php echo $language->lang_echo('ID') ?></th>
            <th class='head0'><?php echo $language->lang_echo('DESCRIPTION') ?></th>
            <th class='head1'><?php echo $language->lang_echo('HOURS') ?></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach($this->get('myHours') as $hours): ?>
        <tr>
            <td><?php echo $this->displayLink('timesheets.editTime', $hours['id'], array('id' => $hours['id'])) ?></td>
            <td><?php echo $this->displayLink('timesheets.editTime', $hours['description'], array('id' => $hours['id'])) ?></td>
            <td><?php echo $hours['hours'] ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
