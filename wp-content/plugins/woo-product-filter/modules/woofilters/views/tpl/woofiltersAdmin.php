<section>
    <div class="supsystic-item supsystic-panel">
        <div id="containerWrapper">
            <ul id="wpfTableTblNavBtnsShell" class="supsystic-bar-controls">
                <li title="<?php _e('Delete selected', WPF_LANG_CODE)?>">
                    <button class="button" id="wpfTableRemoveGroupBtn" disabled data-toolbar-button>
                        <i class="fa fa-fw fa-trash-o"></i>
						<?php _e('Delete selected', WPF_LANG_CODE)?>
                    </button>
                </li>
                <li title="<?php _e('Search', WPF_LANG_CODE)?>">
                    <input id="wpfTableTblSearchTxt" type="text" name="tbl_search" placeholder="<?php _e('Search', WPF_LANG_CODE)?>">
                </li>
            </ul>
            <div id="wpfTableTblNavShell" class="supsystic-tbl-pagination-shell"></div>
            <div style="clear: both;"></div>
            <hr />
            <table id="wpfTableTbl"></table>
            <div id="wpfTableTblNav"></div>
            <div id="wpfTableTblEmptyMsg" style="display: none;">
                <h3><?php printf(__('You have no Filters for now. <a href="%s" style="font-style: italic;">Create</a> your Filter!', WPF_LANG_CODE), $this->addNewLink)?></h3>
            </div>
        </div>
        <div style="clear: both;"></div>
        <div id="prewiew" style="margin-top: 30px"></div>
    </div>
</section>
