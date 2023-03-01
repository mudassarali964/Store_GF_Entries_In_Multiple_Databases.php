<?php

  //////////////////////////////// START ///////////////////////////////////////////
 /////// Gravity Form entry from staging database to development database /////////
//////////////////////////////////////////////////////////////////////////////////

/**
 *  Connect with development database
 * */
function connect_another_db() {
    global $second_db;
    $second_db = new wpdb('username', 'password', 'database', 'host');
}
add_action('init', 'connect_another_db');

function save_entry_in_second_db($entry, $form) {
    $gfEntry = getEntryFromStagingDB();
    if ( empty($gfEntry) ) { return; }

    // Save entry in the second database
    createEntryInDevelopmentDB((array) $gfEntry);

    $gfEntryMeta = getEntryMetaByEntryIdFromStagingDB($gfEntry->id);
    if ( empty($gfEntryMeta) ) { return; }

    // Save entry meta in the second database
    createEntryMetaInDevelopmentDB($gfEntry->id, $gfEntryMeta);
}
add_action('gform_after_submission', 'save_entry_in_second_db', 10, 2);

function createEntryInDevelopmentDB($entry) {
    global $second_db;
    // Check if the given entry is already exist or not in the development database
    $entryExist = getEntryByIdFromDevelopmentDB($entry['id']);
    if ($entryExist) { return; }

    $second_db->insert('wp_gf_entry', $entry);
    if($second_db->last_error !== '') {
        error_log('Error saving entry in second database: ' . $second_db->last_error);
    }

    return $second_db->insert_id;
}

function getEntryFromStagingDB() {
    global $wpdb;
    return $wpdb->get_row("SELECT * FROM wp_gf_entry 
                                    ORDER BY id DESC limit 1");
}

function getEntryByIdFromDevelopmentDB($entryId) {
    global $second_db;
    return $second_db->get_row("SELECT id FROM wp_gf_entry 
                                        WHERE id = {$entryId}");
}

function getEntryMetaByEntryIdFromStagingDB($entryId) {
    global $wpdb;
    return $wpdb->get_results("SELECT * FROM wp_gf_entry_meta 
                                    WHERE entry_id = {$entryId}");
}

function createEntryMetaInDevelopmentDB($entryId, $entryMeta) {
    global $second_db;
    // Check if the given entry is already exist or not in the development database
    $entryMetaExist = getEntryMetaByEntryIdFromDevelopmentDB($entryId);
    if ($entryMetaExist) { return; }

    foreach ($entryMeta as $meta) {
        $entryMetaData = [
            'entry_id' => $meta->entry_id,
            'form_id' => $meta->form_id,
            'meta_key' => $meta->meta_key,
            'meta_value' => $meta->meta_value,
            'item_index' => $meta->item_index,
        ];

        $second_db->insert( 'wp_gf_entry_meta', $entryMetaData);
    }

    return;
}

function getEntryMetaByEntryIdFromDevelopmentDB($entryId) {
    global $second_db;
    return $second_db->get_row("SELECT entry_id FROM wp_gf_entry 
                                        WHERE entry_id = {$entryId}");
}

  //////////////////////////////// END ///////////////////////////////////////////
 /////// Gravity Form entry from staging database to development database /////////
//////////////////////////////////////////////////////////////////////////////////