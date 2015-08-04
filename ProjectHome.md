Web based application to (privately) share files with each other.

Features:
  * Share files based on group memberships (or public)
  * Share files with email invites
  * HTML5 WebM video/Ogg audio transcodes for all uploaded video and audio files supported by ffmpeg
    * With transcoding progress information
  * CouchDB NoSQL backend (not really a feature...)
  * Tagging
  * Authentication using simpleSAMLphp (Federated Identities)
  * OpenSocial API (osapi) support for accessing the groups of a user through an OpenSocial container
  * FileAPI (HTML5) (chuncked) file upload
    * Only Firefox 4 is currently supported

Future Features:
  * Chunked file upload in Google Chrome, Safari, IE9 with FileAPI labs extension
  * Allow REST/RPC calls with OAuth to allow other applications access (works for file list so far)
  * OpenSocial widget (not yet started, depends on previous item)
  * Full Text Search (using elasticsearch, WIP)