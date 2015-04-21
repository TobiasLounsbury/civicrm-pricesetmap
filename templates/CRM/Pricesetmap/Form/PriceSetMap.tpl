
<div id="help">
    {ts}<strong>Edit Settings</strong>{/ts}
</div>

<div id="id_pricesetmap" class="crm-block crm-form-block crm-contribution-contributionpage-pricesetmap-form-block">

    <!--<div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div>//-->

    <table class="form-layout-compressed">
        <tr class="crm-contribution-contributionpage-pricesetmap-form-block-is_active">
            <td class="label">{$form.pricesetmap_active.label}</td>
            <td class="html-adjust">{$form.pricesetmap_active.html}<br/>
                <span class="description">{ts}Would you like to enabled a PriceSet Map for this Online Contributions page?{/ts}</span>
            </td>
        </tr>
    </table>

    <fieldset id="PriceSetMapSettings">
        <legend>PriceSet Map Settings</legend>

        <!-- PriceSet->Relationship //-->
        {if $Price}
            <h3 class="pricesetmap-section">Add a Relationship</h3>
        <div id="RelationshipSettings">
            <table class="form-layout-compressed">
                <tr>
                    <td class="label">{$form.PriceFields.label}</td>
                    <td class="html-adjust">{$form.PriceFields.html}</td>
                </tr>
                <tr>
                    <td class="label"><label>Field Value</label></td>
                    <td class="html-adjust"><select id="FieldValue" class="crm-form-select"></select></td>
                </tr>
            </table>
            <div id="RelationshipEditor"></div>
        </div>
    {if $ShowMatchMembership}
        <table class="form-layout-compressed">
            <tr>
                <td class="label"></td>
                <td class="html-adjust"><input type="checkbox" id="relationship_date_match" name="relationship_date_match" /><label for="relationship_date_match">Match Relationship Dates to Membership Dates</label></td>
            </tr>
        </table>
    {/if}
        <input type="button" id="AddRelationship" value="Add Relationship Map" />

        <br /><br /><hr /><br />
        {/if}

        <!-- Controls for adding Maps from priceSetItem -> CustomData //-->
        <!--/<div id="CustomSettings"></div>
        <input type="button" id="AddCustom" value="Add Custom Data Map" />

        <br /><br /><hr /><br />
         //-->

        <!-- Display Current Maps //-->
        <h3 class="pricesetmap-section">Current Mappings</h3>
        <div id="CurrentDetails">
        {foreach from=$details item=row}
            <div class='CurrentDetailsItem'>
                {if $row.type eq "Relationship"}
                    {$row.id} - {$row.field_name} : {$row.field_value_name} => {$row.relationship_type_name} {$row.related_contact_name}
                {elseif $row.type eq "Custom"}

                {/if}
                <span class='DetailDelete' onclick='DeleteDetailRow({$row.id},this)'>Delete</span>
            </div>
        {/foreach}
        </div>
    </fieldset>


   <!--<div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>-->
</div>
<style type="text/css">
{literal}
#RelationshipSettings .crm-submit-buttons,
.crm-relationship-form-block-is_permission_a_b,
.crm-relationship-form-block-is_permission_b_a,
.crm-relationship-form-block-is_active,
.crm-relationship-form-block-description {
    display: none;
}
.CurrentDetailsItem {
    border: 1px solid #aaa;
    padding: 3px;
    margin: 3px;
}

span.DetailDelete:hover {
    text-decoration: underline;
    background-color: #fff;
}
span.DetailDelete {
    float: right;
    text-decoration: none;
    cursor: pointer;
    color: #2786c2;
}

h3.pricesetmap-section {
    background-color: #2786c2 !important;
    font-size: 13px;
    border: 1px solid #333;
}

{/literal}
</style>

<script type="text/javascript">
    {literal}
    CRM.$(function($) {

    {/literal}
    {if $Price}

        {literal}
        var PriceValues = {/literal}{$values}{literal};
        var PriceFields = {/literal}{$fields}{literal};

        cj("#PriceFields").change(function () {
            cj("#FieldValue option").remove();
            for(var v in PriceValues[cj(this).val()]) {
                 cj("#FieldValue").append("<option value='" + v + "'>" + PriceValues[cj(this).val()][v] + "</option>");
            }
        });
        cj("#PriceFields").trigger("change");

        //Load the standard relationship editor into the div provided
        var rsp = CRM.loadForm(CRM.url('civicrm/contact/view/rel', 'cid=1&action=add&reset=1'), {target:"#RelationshipEditor", dialog: false, autoClose:false});

        rsp.on("crmFormSuccess", function(e, data) {
            alert("test");
        });


        //Add a new Relationship Map
        cj("#AddRelationship").click(function (e) {
            if (cj("#relationship_date_match").is(":checked")) {
                var matchDate = 1;
            } else {
                var matchDate = 0;
            }
            CRM.api3("PriceSetMapDetail", "Update", {
                "page_id": {/literal}{$PageID}{literal},
                "type": "Relationship",
                "relationship_type": cj("#relationship_type_id").val(),
                "related_contact": cj("#related_contact_id").val(),
                "start_date": cj("#start_date").val(),
                "end_date": cj("#end_date").val(),
                "notes": cj("#note").val(),
                "field_id": cj("#PriceFields").val(),
                "field_value": cj("#FieldValue").val(),
                "relationship_date_match_membership": matchDate,
                "debug": 1
            }).done(function(result) {
                if (!result.is_error && result.values) {
                    var Names = "";
                    cj("#s2id_related_contact_id li div").each(function() {
                        Names = Names + cj(this).text() + ", ";
                    });

                    cj("#CurrentDetails").append("<div class='CurrentDetailsItem'>" + result.values + " - " + cj("#PriceFields option:selected").text() + " : " + cj("#FieldValue option:selected").text() + " => " + cj("#relationship_type_id option:selected").text() + " " + Names + "<span class='DetailDelete' onclick='DeleteDetailRow("+result.values+",this)'>Delete</span></div>");
                }
            });

        });


        {/literal}
    {/if}
    {literal}


        //Add a new Custom Data Map
        cj("#AddCustom").click(function (e) {
            //TODO: Actually Add something
            alert("test");
        });

        // bind click event to pricesetmap_active checkbox
        cj('#pricesetmap_active').click(function () {
            PriceSetMapSettingsBlock();
        });

        // hide settings if not enabled
        if (!cj('#pricesetmap_active').prop('checked')) {
            cj('#PriceSetMapSettings').hide();
        }
    });

    // function to delete rows
    function DeleteDetailRow(rowId, obj) {
        if (confirm('Are you sure you want to delete this row from the database?')) {
            CRM.api3('PriceSetMapDetail', 'Delete', {
                "id": rowId,
            }).done(function(result) {
                if(result['is_error'] == 0 && result.values == 1) {
                    //Delete the Dom elements
                    cj(obj).closest(".CurrentDetailsItem").remove();
                }
            });
        }
    }

    // function to show/hide settings
    function PriceSetMapSettingsBlock() {
        var psm_status = 0;
        if (cj('#pricesetmap_active').prop('checked')) {
            psm_status = 1;
        }
        CRM.api3('PriceSetMap', 'Toggle', {
            "sequential": 1,
            "page_id": {/literal}{$PageID}{literal},
            "status": psm_status
        }).done(function(result) {
            // do something
            if (result.is_error) {
                CRM.alert(result.error_message, "error");
            } else {
                if (cj('#pricesetmap_active').prop('checked')) {
                    cj('#PriceSetMapSettings').slideDown("fast");
                }
                else {
                    cj('#PriceSetMapSettings').slideUp("fast");
                }
            }
        });

        return false;
    }

    {/literal}
</script>


