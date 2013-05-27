<?php
/**
 * Doctrine DBAL server storage class for LEOP's OAuth2 server library
 * 
 * Why? Because the built-in one uses zetacomponents/database and the rest of
 * your app uses Doctrine2 ORM or DBAL. Using this DBAL implementation offers
 * no other advantages; zetacomponents/database and doctrine/dbal are roughly
 * equivalent.
 */
namespace Inanimatt\OAuth2\Server\Storage\DBAL;

use League\OAuth2\Server\Storage\ScopeInterface;

class Scope implements ScopeInterface
{
    protected $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getScope($scope, $clientId = null, $grantType = null)
    {
        $stmt = $this->db->prepare('SELECT * FROM oauth_scopes WHERE oauth_scopes.scope = :scope');
        $stmt->bindValue(':scope', $scope);
        $stmt->execute();

        $row = $stmt->fetch(\PDO::FETCH_OBJ);

        if ($row === false) {
            return false;
        }

        return array(
            'id' =>  $row->id,
            'scope' =>  $row->scope,
            'name'  =>  $row->name,
            'description'  =>  $row->description
        );

    }
}
