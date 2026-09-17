# PushCow

Push notification micro-service built on top of Firebase Cloud Messaging (FCM) and Huawei Push Service (HPS).

## Setup

1. ```composer install```
2. ```php artisan migrate```
3. ```php artisan register:app {name} {--api-version=3} {--service-account=} {--key=} {--sender=} {--hps-client-id=} {--hps-secret=}```
   - `--service-account`: the Firebase service account name (see step 4 below). Required to send through FCM.
   - `--key` / `--sender`: reserved for the legacy FCM server key/sender ID. Not used since FCM HTTP v1 became the only supported protocol.
   - `--hps-client-id` / `--hps-secret`: Huawei Push Service (HPS) credentials. Required to send to `HUAWEI` devices.
4. Place the service account private key under ```storage/service-accounts/```. The service account private key file name must match the service account name without the extension during app registration.

### Queues
1. message-requests
2. create-message
3. forward-message

## API

The PushCow API complies with REST and [JSend](https://github.com/omniti-labs/jsend) with proper HTTP status code responses.

The current API version is `v3`; all endpoints below are prefixed with `/api/v3`.

### Authentication

The PushCow API requires authentication for all requests made on behalf of an application.  
Authenticated requests require a **Bearer Token** (`Authorization: Bearer <token>`).  
These tokens are unique to an application and should be stored securely.

A missing or invalid token returns a `401` with a JSend `error` response.

### Error Responses

Validation failures return a `422` with a JSend `fail` response, where `data` maps each
invalid field to its error messages:

```json
{
  "status": "fail",
  "data": {
    "device_id": ["The device id field is required."],
    "token": ["The token field is required."]
  }
}
```

Any other error (authentication, not found, unexpected server errors, etc.) returns a JSend
`error` response with the appropriate HTTP status code:

```json
{
  "status": "error",
  "message": "Unauthenticated."
}
```

### Endpoints

#### Pulse

To determine whether the application is alive and the last received/pushed event.  
This is useful for checking if the service is up and running healthily.  

**Endpoint**: `GET /`

**Example usage**:

```bash
curl -H "Authorization: Bearer *******" https://<domain>/api/v3
```

**Example output**:

```json
{
  "status": "success",
  "data": {
    "name": "PING",
    "last_received_at": null,
    "last_pushed_at": null
  }
}
```

#### Register Device

To register or update an existing device.

**Endpoint**: `POST /devices`

**Body**:

| Parameter    | Description                                                                 |
|--------------|-----------------------------------------------------------------------------|
| `device_id`* | The device ID.                                                              |
| `token`*     | The device token. Unique.                                                  |
| `user_id`    | The application user ID. User binding will be removed if the field is empty. |
| `platform`   | The device platform. Platforms: `ANDROID`, `IOS`, `HUAWEI`. Determines whether messages are sent through FCM or Huawei Push Service. |

#### Unregister Device

To unregister an existing device.

**Endpoint**: `DELETE /devices`

**Body**:

| Parameter    | Description                                                                 |
|--------------|-----------------------------------------------------------------------------|
| `device_id`  | The device ID. Required without `token` or `user_id`.                       |
| `token`      | The device token. Required without `device_id` or `user_id`.                |
| `user_id`    | The application user ID. Required without `device_id` or `token`.           |

#### Create Message

Create a new message.

**Endpoint**: `POST /messages`

**Body**:

| Parameter       | Description                                                                                              |
|-----------------|----------------------------------------------------------------------------------------------------------|
| `recipients`*   | The recipients’ user ID or token. Both string and array are accepted.                                    |
| `notification`* | The notification content in JSON format. The title and body are required. E.g. `{"title": "PushCow", "body": "Moo moo!"}` |
| `data`          | The data to be handled by the client app in JSON format. `_user_id` will be prepended where applicable.  |
| `options`       | The options to be handled by the platform provider.                                                      |

## How To Use API

### Register Device API

- Use the API to register the device ID, token, and user ID into PushCow.
- The API can be used multiple times. PushCow keeps a single record per `device_id` and updates it in
  place, including when the token changes (e.g. after an OS-level push token rotation) — it will never
  create a duplicate record for the same `device_id`.
- Example:
  - **Action**: Installed and opened the app without login.  
    **Process**: Register the device ID and token into PushCow.
  - **Action**: Logged into the app.  
    **Process**: Update the User ID with the previously registered device ID and token.

### Unregister Device API

- Use the API to delete a device or user ID from the record.
- Once deleted, the device will not be able to receive any notifications.
- This API can also be used in preference settings, not just on logout.
- Deleting by `user_id` removes every device (and its token) currently bound to that user, which is
  useful for revoking all of a user's devices at once (e.g. "log out everywhere").

### Create Message API

- The Create Message API has three methods:
  1. **Send to all**:  
     Set `*` on recipients.
  2. **Send to selected users**:  
     Set `recipients` to an array (e.g. `["device-id-1", "user-id-2"]`), or an equivalent JSON-array
     string (e.g. `'["device-id-1", "user-id-2"]'`), of device IDs, tokens, and/or user IDs.
  3. **Send to all except selected users**:  
     Set a JSON string, e.g., `{"except": ["user-id-1"]}`.

### Logout

- On logout, use the Unregister Device API and Register Device API based on the situation.
