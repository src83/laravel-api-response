<?php

/**
 * Dictionary of operation results
 * API returns a result message from api_results dictionary
 */

// EN
return [

    // Action-Based Success Messages

    'ok'          => 'Ok',
    'success'     => 'Success',

    // CRUD BASE
    'created'     => 'Record has been successfully created',
    'updated'     => 'Record has been successfully updated',
    'deleted'     => 'Record has been successfully deleted',

    // VISIBILITY / STATUS
    'published'   => 'Record has been published',
    'unpublished' => 'Record has been unpublished',
    'activated'   => 'Record has been activated',
    'deactivated' => 'Record has been deactivated',
    'enabled'     => 'Feature has been enabled',
    'disabled'    => 'Feature has been disabled',

    // CONFIRMATION / CHECK
    'checked'     => 'Check completed successfully',
    'verified'    => 'Successfully verified',
    'validated'   => 'Data has been successfully validated',
    'invalidated' => 'Data has been invalidated',
    'approved'    => 'The request has been approved',
    'rejected'    => 'The request has been rejected',

    // TRANSFER / IO
    'uploaded'    => 'File has been uploaded',
    'downloaded'  => 'File has been downloaded',
    'imported'    => 'Data has been imported',
    'exported'    => 'Data has been exported',

    // OTHER COMMON ACTIONS
    'sent'        => 'Sent',
    'received'    => 'Received',
    'synced'      => 'Synchronized',
    'attached'    => 'Attached',
    'detached'    => 'Detached',
    'assigned'    => 'Assigned',
    'unassigned'  => 'Unassigned',

    // HTTP Error Messages

    'http_error'            => 'HTTP Error',

    'bad_request'           => 'Bad Request',  // 400
    'unauthorized'          => 'Unauthenticated',  // 401
    'forbidden'             => 'Unauthorized (permission denied)',  // 403
    'item_not_found'        => 'Item not found',  // 404
    'model_not_found'       => 'Model not found',  // 404
    'not_found'             => 'Not found',  // 404
    'method_not_allowed'    => 'Method not allowed',  // 405
    'conflict'              => 'Conflict',  // 409
    'content_too_large'     => 'Content too large',  // 413
    'unprocessable_content' => 'Validation error',  // 422
    'locked'                => 'Resource locked',  // 423
    'internal_server_error' => 'Internal Server Error',  // 500

    // Error Messages by Modules (check documentation)

];
