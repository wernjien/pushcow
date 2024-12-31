# PushCow

Push notification micro-service built on top of Firebase Cloud Messaging (FCM).

## API

The PushCow API complies with REST and JSend with proper HTTP status code responses.

### Authentication

The PushCow API requires authentication for all requests made on behalf of an application.  
Authenticated requests require a **Bearer Token** (`Authorization: Bearer <token>`).  
These tokens are unique to an application and should be stored securely.

### Endpoints

#### Pulse

To determine whether the application is alive and the last received/pushed event.  
This is useful for checking if the service is up and running healthily.  
Bearer token is required, please contact [Wern Jien](mailto:wj@innoractive.com) for a token.

**Endpoint**: `GET /`

**Example usage**:

```bash
curl -H "Authorization: Bearer *******" https://<domain>/api/v1
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

#### Unregister Device

To unregister an existing device.

**Endpoint**: `DELETE /devices`

**Body**:

| Parameter    | Description                                                                 |
|--------------|-----------------------------------------------------------------------------|
| `device_id`  | The device ID. Required without `token` or `user_id`.                       |
| `token`      | The device token. Required without `device_id` or `user_id`.                |
| `user_id`    | The application user ID. Required without `device_id` or `token`.           |
| `platform`   | The device platform. Platforms: `ANDROID`, `IOS`, `HUAWEI`                  |

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
- The API can be used multiple times. PushCow will update the record without adding a new one, except if the device uses a different token.
- Example:
  - **Action**: Installed and opened the app without login.  
    **Process**: Register the device ID and token into PushCow.
  - **Action**: Logged into the app.  
    **Process**: Update the User ID with the previously registered device ID and token.

### Unregister Device API

- Use the API to delete a device or user ID from the record.
- Once deleted, the device will not be able to receive any notifications.
- This API can also be used in preference settings, not just on logout.
- Old tokens registered on PushCow and handled by the backend will be removed.

### Create Message API

- The Create Message API has three methods:
  1. **Send to all**:  
     Set `*` on recipients.
  2. **Send to selected users**:  
     Set an array of selected device IDs or user IDs in a string.
  3. **Send to all except selected users**:  
     Set a JSON string, e.g., `{"except": ...}`.

### Logout

- On logout, use the Unregister Device API and Register Device API based on the situation.
