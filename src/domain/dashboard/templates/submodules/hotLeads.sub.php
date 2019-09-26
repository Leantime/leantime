
<table class='table table-bordered'>
    <colgroup>
        <col class="con1" />
          <col class="con0"/>
        <col class="con1" />
    </colgroup>
    <thead>
        <tr>
            <th class='head0'><?php echo $language->lang_echo('ID') ?></th>
            <th class='head1'><?php echo $language->lang_echo('NAME') ?></th>
            <th class='head0'><?php echo $language->lang_echo('CLIENT') ?></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($this->get('hotLeads') as $lead): ?>
            <tr>
                <td>
        <?php echo $this->displayLink('leads.showLead', $lead['id'], array('id' => $lead['id'])); ?>
                </td>
                <td>
        <?php echo $this->displayLink('leads.showLead', $lead['name'], array('id' => $lead['id'])); ?>
                </td>
                <td><?php echo $lead['status'] ?></td>
            </tr>
    <?php endforeach; ?>
    </tbody>
</table>
