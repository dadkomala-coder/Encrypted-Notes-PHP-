# Encrypted Notes (PHP)

A simple **self-hosted encrypted notes application written in pure
PHP**. Notes are encrypted with a password and stored as encrypted files
on disk. Only notes encrypted with the **same password** can be viewed
or deleted.

This project is designed to be **lightweight, private, and
dependency-free**.

------------------------------------------------------------------------

## Features

-   Strong encryption (AES-256-CBC)
-   Save encrypted notes
-   View all notes using the same password
-   Delete notes (requires password)
-   Notes stored as encrypted `.enc` files
-   No database required
-   Single PHP file application
-   Works on any basic PHP hosting

------------------------------------------------------------------------

## How It Works

Each note is encrypted using:

-   AES-256-CBC for encryption
-   HMAC-SHA256 for authentication
-   SHA-512 for key derivation

When you enter a password:

1.  The password generates encryption and authentication keys.
2.  The note is encrypted before saving.
3.  When viewing notes, the script tries to decrypt each file using the
    provided password.
4.  Only notes encrypted with that password will be displayed.

This allows multiple password groups of notes inside the same storage
directory.

------------------------------------------------------------------------

## Project Structure

project/ │ ├── ver2.php \# main application └── notes/ \# encrypted
notes storage

Each note is saved as:

notes/1.enc notes/2.enc notes/3.enc

------------------------------------------------------------------------

## Installation

1.  Download the repository

git clone https://github.com/dadkomala-coder/encrypted-notes

or download the ZIP archive.

2.  Upload files to your server.

3.  Make sure PHP can write to the `notes` directory.

Example:

chmod 755 notes

4.  Open the script in your browser:

http://yourserver/encrypted_notes.php

------------------------------------------------------------------------

## Usage

### Add Note

1.  Write your note
2.  Enter a password
3.  Click **Save**

The note will be encrypted and stored on disk.

### View Notes

1.  Enter the password
2.  Click **Show Notes**

Only notes encrypted with that password will appear.

### Delete Note

To delete a note:

1.  Enter the same password used to create it
2.  Click **Delete**

If the password is incorrect, deletion will fail.

------------------------------------------------------------------------

## Security Notes

This project uses:

-   AES-256-CBC encryption
-   HMAC authentication
-   constant-time MAC comparison (`hash_equals`)

This project is intended mainly for personal or self-hosted usage.

------------------------------------------------------------------------

## Requirements

-   PHP 7.4+
-   OpenSSL extension enabled

Most hosting providers support this by default.

------------------------------------------------------------------------

## License

MIT License
