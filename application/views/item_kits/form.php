<div id="required_fields_message" class="obligatoire" style="margin-bottom:8px;">
    <?php echo $this->lang->line('common_fields_required_message'); ?>
</div>
<ul id="error_message_box" style="color:#ef4444;margin-bottom:8px;"></ul>

<?php echo form_open('item_kits/save/'.$item_kit_info->item_kit_id, array('id'=>'item_kit_form')); ?>

<div class="md-card" style="margin-bottom:12px;">
    <div class="md-card-title">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
        <?php echo $this->lang->line("item_kits_info"); ?>
    </div>
    <div class="md-form-row">
        <div class="md-form-group" style="flex:1">
            <label class="md-form-label required"><?php echo $this->lang->line('item_kits_name'); ?></label>
            <?php echo form_input(array(
                'name'=>'name','id'=>'name',
                'class'=>'md-form-input required',
                'value'=>$item_kit_info->name
            )); ?>
        </div>
    </div>
    <div class="md-form-group" style="margin-top:8px;">
        <label class="md-form-label"><?php echo $this->lang->line('item_kits_description'); ?></label>
        <?php echo form_textarea(array(
            'name'=>'description','id'=>'description',
            'class'=>'md-form-input',
            'value'=>$item_kit_info->description,
            'rows'=>'4',
            'style'=>'resize:vertical;'
        )); ?>
    </div>
    <div class="md-form-group" style="margin-top:8px;">
        <label class="md-form-label"><?php echo $this->lang->line('item_kits_add_item'); ?></label>
        <?php echo form_input(array(
            'name'=>'item','id'=>'item',
            'class'=>'md-form-input',
            'placeholder'=>$this->lang->line('item_kits_add_item')
        )); ?>
    </div>
</div>

<div class="md-card" style="margin-bottom:12px;">
    <div class="md-card-title">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
        <?php echo $this->lang->line('item_kits_item'); ?>
    </div>
    <table id="item_kit_items" style="width:100%;border-collapse:collapse;">
        <thead>
            <tr>
                <th style="font-size:0.75em;font-weight:600;text-transform:uppercase;color:#fff;background:#4386a1cc;padding:6px 10px;text-align:center;width:50px;"><?php echo $this->lang->line('common_delete'); ?></th>
                <th style="font-size:0.75em;font-weight:600;text-transform:uppercase;color:#fff;background:#4386a1cc;padding:6px 10px;text-align:left;"><?php echo $this->lang->line('item_kits_item'); ?></th>
                <th style="font-size:0.75em;font-weight:600;text-transform:uppercase;color:#fff;background:#4386a1cc;padding:6px 10px;text-align:center;width:80px;"><?php echo $this->lang->line('item_kits_quantity'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($this->Item_kit_items->get_info($item_kit_info->item_kit_id) as $item_kit_item) { ?>
            <tr>
                <?php $item_info = $this->Item->get_info($item_kit_item['item_id']); ?>
                <td style="text-align:center;padding:4px 10px;border-bottom:1px solid var(--border-color,#e2e8f0);">
                    <a href="#" onclick="return deleteItemKitRow(this);" style="text-decoration:none;">
                        <svg width="16" height="16" fill="none" stroke="#ef4444" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
                    </a>
                </td>
                <td style="padding:4px 10px;border-bottom:1px solid var(--border-color,#e2e8f0);"><?php echo $item_info->name; ?></td>
                <td style="text-align:center;padding:4px 10px;border-bottom:1px solid var(--border-color,#e2e8f0);">
                    <input class="quantity md-form-input" id="item_kit_item_<?php echo $item_kit_item['item_id']; ?>" type="text" size="3" name="item_kit_item[<?php echo $item_kit_item['item_id']; ?>]" value="<?php echo $item_kit_item['quantity']; ?>" style="text-align:center;width:60px;"/>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<div style="display:flex;justify-content:flex-end;">
    <?php echo form_submit(array(
        'name'=>'submit','id'=>'submit',
        'value'=>$this->lang->line('common_submit'),
        'class'=>'btsubmit'
    )); ?>
</div>

<?php echo form_close(); ?>

<script type="text/javascript">
/*
$("#item").autocomplete('<?php //echo site_url("items/item_search"); ?>',
{
    minChars:0,
    max:100,
    selectFirst: false,
    delay:10,
    formatItem: function(row) {
        return row[1];
    }
});

$("#item").result(function(event, data, formatted)
{
    $("#item").val("");

    if ($("#item_kit_item_"+data[0]).length ==1)
    {
        $("#item_kit_item_"+data[0]).val(parseFloat($("#item_kit_item_"+data[0]).val()) + 1);
    }
    else
    {
        $("#item_kit_items tbody").append("<tr><td style='text-align:center;padding:4px 10px;border-bottom:1px solid var(--border-color,#e2e8f0);'><a href='#' onclick='return deleteItemKitRow(this);' style='text-decoration:none;'><svg width='16' height='16' fill='none' stroke='#ef4444' stroke-width='2' viewBox='0 0 24 24'><polyline points='3 6 5 6 21 6'/><path d='M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2'/></svg></a></td><td style='padding:4px 10px;border-bottom:1px solid var(--border-color,#e2e8f0);'>"+data[1]+"</td><td style='text-align:center;padding:4px 10px;border-bottom:1px solid var(--border-color,#e2e8f0);'><input class='quantity md-form-input' id='item_kit_item_"+data[0]+"' type='text' size='3' name=item_kit_item["+data[0]+"] value='1' style='text-align:center;width:60px;'/></td></tr>");
    }
});


//validation and submit handling
$(document).ready(function()
{
    $('#item_kit_form').validate({
        submitHandler:function(form)
        {
            $(form).ajaxSubmit({
            success:function(response)
            {
                tb_remove();
                post_item_kit_form_submit(response);
            },
            dataType:'json'
        });

        },
        errorLabelContainer: "#error_message_box",
        wrapper: "li",
        rules:
        {
            name:"required",
            category:"required"
        },
        messages:
        {
            name:"<?php //echo $this->lang->line('items_name_required'); ?>",
            category:"<?php //echo $this->lang->line('items_category_required'); ?>"
        }
    });
});

function deleteItemKitRow(link)
{
    $(link).parent().parent().remove();
    return false;
}//*/
</script>
