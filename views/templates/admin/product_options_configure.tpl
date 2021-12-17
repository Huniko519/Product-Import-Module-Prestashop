<div class="panel">
    <h3>Purechoice Product Options</h3>

    <div class="form-group">
        <label class="control-label col-lg-3" for="pc_po_upc">UPC</label>

        <div class="col-lg-2">
            <input id="pc_po_upc" type="text" name="pc_po_upc" class="form-control" value="{$pc_po_upc}">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-lg-3" for="pc_po_caliber">Caliber</label>

        <div class="col-lg-2">
            <input id="pc_po_caliber" type="text" name="pc_po_caliber" class="form-control" value="{$pc_po_caliber}">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-lg-3" for="pc_po_velocity">Velocity</label>

        <div class="col-lg-2">
            <input id="pc_po_velocity" type="text" name="pc_po_velocity" class="form-control" value="{$pc_po_velocity}">
        </div>
    </div>

    <div class="panel-footer">
        <a href="index.php?controller=AdminProducts&amp;token={$token}" class="btn btn-default"><i class="process-icon-cancel"></i> Cancel</a>
        <button type="submit" name="submitAddproduct" class="btn btn-default pull-right">
            <i class="process-icon-save"></i> Save
        </button>
        <button type="submit" name="submitAddproductAndStay" class="btn btn-default pull-right">
            <i class="process-icon-save"></i> Save and stay
        </button>
    </div>
</div>
