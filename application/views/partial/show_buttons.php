<!-- Modern Action Buttons - YesAppro Style -->
<div class="btn-group-modern">
<?php
    // Business action buttons only when not in undelete mode
    if (($_SESSION['undel'] ?? 0) != 1)
    {
        // NEW button (Ajouter)
        if (($_SESSION['G']->modules[$_SESSION['module_id']]['show_new_button'] ?? 0) == 1)
        {
            echo anchor(
                $_SESSION['G']->modules[$_SESSION['module_id']]['module_name'].'/view/-1',
                '<svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/></svg>'
                .$this->lang->line($_SESSION['G']->modules[$_SESSION['module_id']]['module_name'].'_new'),
                'class="btn-modern-primary"'
            );
        }

        // UNDELETE button (Réactiver) - masqué pour items car remplacé par toggle
        if (($_SESSION['G']->modules[$_SESSION['module_id']]['show_undel_button'] ?? 0) == 1
            && ($_SESSION['G']->modules[$_SESSION['module_id']]['module_name'] ?? '') != 'items')
        {
            echo anchor(
                $_SESSION['G']->modules[$_SESSION['module_id']]['module_name'].'/list_deleted/',
                '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>'
                .$this->lang->line($_SESSION['G']->modules[$_SESSION['module_id']]['module_name'].'_undelete'),
                'class="btn-modern-primary"'
            );
        }

        // MERGE button (Fusionner)
        if (($_SESSION['G']->modules[$_SESSION['module_id']]['show_merge_button'] ?? 0) == 1)
        {
            echo anchor(
                $_SESSION['G']->modules[$_SESSION['module_id']]['module_name'].'/merge_form/',
                '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4-4m-4 4l4 4"/></svg>'
                .$this->lang->line($_SESSION['G']->modules[$_SESSION['module_id']]['module_name'].'_merge'),
                'class="btn-modern-primary"'
            );
        }

        // CLONE button (Cloner) — if single item selected, pass it as source
        if (($_SESSION['G']->modules[$_SESSION['module_id']]['show_clone_button'] ?? 0) == 1)
        {
            $clone_base = site_url($_SESSION['G']->modules[$_SESSION['module_id']]['module_name'].'/clone_form');
            echo '<a href="'.$clone_base.'" class="btn-modern-primary" onclick="var sel=get_selected_values();if(sel.length===1){this.href=\''.$clone_base.'/\'+encodeURIComponent(sel[0]);}return true;">'
                .'<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>'
                .$this->lang->line($_SESSION['G']->modules[$_SESSION['module_id']]['module_name'].'_clone')
                .'</a>';
        }

        // BULK EDIT dropdown (Modification multiple) - items only
        if (($_SESSION['G']->modules[$_SESSION['module_id']]['module_name'] ?? '') == 'items')
        {
            echo '<div class="bulk-dropdown-wrapper">';
            echo '<a href="#" id="bulk_edit_btn" class="btn-modern-primary" onclick="toggleBulkDropdown(event);">'
                .'<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>'
                .$this->lang->line($_SESSION['G']->modules[$_SESSION['module_id']]['module_name'].'_bulk_edit')
                .' <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-left:4px;"><polyline points="6 9 12 15 18 9"/></svg>'
                .'</a>';
            echo '<div id="bulk_dropdown_menu" class="bulk-dropdown-menu">';
            if (!empty($_SESSION['G']->bulk_actions_pick_list)) {
                foreach ($_SESSION['G']->bulk_actions_pick_list as $action_id => $action_label) {
                    if ($action_id === '' || $action_id === 0 || $action_label === '') continue;
                    echo '<a href="#" class="bulk-dropdown-item" onclick="submitBulkEdit(\'' . $action_id . '\'); return false;">'
                        . htmlspecialchars($action_label)
                        . '</a>';
                }
            }
            echo '</div>';
            echo '</div>';
        }
    }

    // EXIT button (Retour) - navigation, uses neutral style
    if (($_SESSION['G']->modules[$_SESSION['module_id']]['show_exit_button'] ?? 0) == 1)
    {
        echo anchor(
            'common_controller/common_exit/',
            '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>'
            .$this->lang->line('common_return'),
            'class="btn-action"'
        );
    }
?>
</div>
