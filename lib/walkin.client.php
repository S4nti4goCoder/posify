<?php

// The walk in customer, for sales where the buyer does not identify.
// Colombia's DIAN expects the document 222222222222 on those.
//
// One record per branch, so the branch filter that protects every other
// listing keeps working with no special cases. A branch gets its own the
// first time its POS is opened.

final class WalkInClient
{
    public const DOCUMENT = "222222222222";
    public const NAME     = "Consumidor";
    public const SURNAME  = "Final";

    /*=============================================
    The POS falls back to this customer on every sale, so it must not be
    deleted. Guards the delete path, whatever route it comes through.
    =============================================*/

    public static function isProtected(string $table, $id): bool
    {
        if ($table !== "clients" || $id === "" || $id === null) {

            return false;
        }

        $record = CurlController::request(
            "clients?linkTo=id_client&equalTo=" . (int) $id . "&select=cc_client",
            "GET",
            array()
        );

        return !empty($record)
            && $record->status == 200
            && (string) $record->results[0]->cc_client === self::DOCUMENT;
    }

    public static function idFor(int $officeId, string $token): ?int
    {
        // 0 es Multi Sucursal, no una sede: no hay a quien facturarle ahi
        if ($officeId <= 0) {

            return null;
        }

        $found = CurlController::request(
            "clients?linkTo=cc_client,id_office_client"
                . "&equalTo=" . self::DOCUMENT . "," . $officeId
                . "&select=id_client",
            "GET",
            array()
        );

        if (!empty($found) && $found->status == 200) {

            return (int) $found->results[0]->id_client;
        }

        return self::create($officeId, $token);
    }

    private static function create(int $officeId, string $token): ?int
    {
        $created = CurlController::request(
            "clients?token=" . $token . "&table=admins&suffix=admin",
            "POST",
            array(
                "cc_client"           => self::DOCUMENT,
                "name_client"         => self::NAME,
                "surname_client"      => self::SURNAME,
                "email_client"        => "",
                "address_client"      => "",
                "phone_client"        => "",
                "id_office_client"    => $officeId,
                "date_created_client" => date("Y-m-d")
            )
        );

        if (!empty($created) && $created->status == 200 && isset($created->results->lastId)) {

            return (int) $created->results->lastId;
        }

        return null;
    }
}
