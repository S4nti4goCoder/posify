<?php

// What may be written into a folder the web server executes from.
//
// The extension used to come straight from the name the browser sent, so a
// file called shell.php was saved as a .php inside cms/views/assets/files,
// which Apache serves and runs. Uploading one was enough to run code on the
// server.
//
// The list is what the file manager exists to hold. svg is left out on
// purpose: it is markup, it can carry script, and it renders inline.

final class UploadGuard
{
    private const ALLOWED = [
        "jpg", "jpeg", "png", "gif", "webp", "bmp", "ico",
        "mp4", "webm", "ogg", "mp3", "wav",
        "pdf", "doc", "docx", "xls", "xlsx", "ppt", "pptx", "csv", "txt", "zip",
    ];

    public static function extensionOf(string $name): string
    {
        $parts = explode(".", $name);

        return strtolower(trim(end($parts)));
    }

    public static function isAllowed(string $name): bool
    {
        return in_array(self::extensionOf($name), self::ALLOWED, true);
    }

    /** A name that cannot climb out of the folder or hide a second extension. */
    public static function safeName(string $name): string
    {
        return uniqid() . getdate()["seconds"] . "." . self::extensionOf($name);
    }

    public static function allowedList(): string
    {
        return implode(", ", self::ALLOWED);
    }
}
