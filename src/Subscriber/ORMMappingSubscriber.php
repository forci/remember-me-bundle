<?php

/*
 * This file is part of the ForciRememberMeBundle package.
 *
 * (c) Martin Kirilov <wucdbm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Forci\Bundle\RememberMe\Subscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\DBAL\Platforms;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Id\AssignedGenerator;
use Doctrine\ORM\Id\BigIntegerIdentityGenerator;
use Doctrine\ORM\Id\IdentityGenerator;
use Doctrine\ORM\Id\SequenceGenerator;
use Doctrine\ORM\Id\UuidGenerator;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\ORMException;
use Forci\Bundle\RememberMe\Entity\DeviceAwareInterface;
use Forci\Bundle\RememberMe\Entity\DeviceAwareTrait;

class ORMMappingSubscriber implements EventSubscriber {

    /** @var string */
    protected $tokenClass;

    /** @var string */
    protected $sessionClass;

    /** @var string */
    protected $tokenRepositoryClass;

    /** @var string */
    protected $sessionRepositoryClass;

    public function __construct(string $tokenClass, string $sessionClass, string $tokenRepositoryClass, string $sessionRepositoryClass) {
        $this->tokenClass = $tokenClass;
        $this->sessionClass = $sessionClass;
        $this->tokenRepositoryClass = $tokenRepositoryClass;
        $this->sessionRepositoryClass = $sessionRepositoryClass;
    }

    public function getSubscribedEvents() {
        return [
            Events::loadClassMetadata
        ];
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs) {
        /** @var ClassMetadata $classMetadata */
        $classMetadata = $eventArgs->getClassMetadata();
        /** @var EntityManager $entityManager */
        $entityManager = $eventArgs->getEntityManager();

        /** @var \ReflectionClass $reflectionClass */
        $reflectionClass = $classMetadata->getReflectionClass();
        if (!$reflectionClass) {
            return;
        }

        if (is_a($reflectionClass->getName(), DeviceAwareInterface::class, true)) {
            $this->mapDeviceAware($classMetadata);
        }

        switch ($reflectionClass->getName()) {
            case $this->tokenClass:
                $this->mapToken($classMetadata, $entityManager);
                break;
            case $this->sessionClass:
                $this->mapSession($classMetadata, $entityManager);
                break;
        }
    }

    protected function mapToken(ClassMetadata $metadata, EntityManager $em) {
        $metadata->setPrimaryTable([
            'name' => 'remember_me__tokens',
            'options' => [
                'collate' => 'utf8mb4_unicode_ci',
                'charset' => 'utf8mb4'
            ]
        ]);

        $metadata->setCustomRepositoryClass($this->tokenRepositoryClass);

        $builder = new ClassMetadataBuilder($metadata);

//        /**
//         * @ORM\Id
//         * @ORM\Column(name="id", type="integer", options={"unsigned"=true}, nullable=false)
//         * @ORM\GeneratedValue(strategy="AUTO")
//         */
//        protected $id;
        if (!$metadata->hasField('id')) {
            $this->mapId($metadata, $em);
        }

//        /**
//         * @ORM\Column(name="series", type="string", length=88, options={"fixed" = true})
//         */
//        protected $series;
        if (!$metadata->hasField('series')) {
            $builder->createField('series', 'string')->columnName('series')->length(88)->option('fixed', true)->build();
            $builder->addUniqueConstraint(['series'], 'series');
//            $builder->createField('series', 'string')->columnName('series')->makePrimaryKey()->length(88)->generatedValue('NONE')->option('fixed', true)->build();
//            $metadata->setIdGenerator(new AssignedGenerator());
        }

//        /**
//         * @ORM\Column(name="value", type="string", length=88, options={"fixed" = true})
//         */
//        protected $value;
        if (!$metadata->hasField('value')) {
            $builder->createField('value', 'string')->columnName('value')->length(88)->option('fixed', true)->build();
        }

//        /**
//         * @ORM\Column(name="lastUsed", type="datetime")
//         */
//        protected $lastUsed;
        if (!$metadata->hasField('lastUsed')) {
            $builder->createField('lastUsed', 'datetime')->columnName('last_used')->build();
        }

//        /**
//         * @ORM\Column(name="dateCreated", type="datetime")
//         */
//        protected $dateCreated;
        if (!$metadata->hasField('dateCreated')) {
            $builder->createField('dateCreated', 'datetime')->columnName('date_created')->build();
        }

//        /**
//         * @ORM\Column(name="class", type="string", length=100)
//         */
//        protected $class;
        if (!$metadata->hasField('class')) {
            $builder->createField('class', 'string')->columnName('class')->length(100)->build();
        }

//        /**
//         * @ORM\Column(name="username", type="string", length=200)
//         */
//        protected $username;
        if (!$metadata->hasField('username')) {
            $builder->createField('username', 'string')->columnName('username')->length(200)->nullable(true)->build();
        }

//        /**
//         * @ORM\Column(name="area", type="string", length=200)
//         */
//        protected $area;
        if (!$metadata->hasField('area')) {
            $builder->createField('area', 'string')->columnName('area')->length(200)->build();
        }

//        /**
//         * @ORM\Column(name="userId", type="integer", options={"unsigned"=true})
//         */
//        protected $userId;
        if (!$metadata->hasField('userId')) {
            $builder->createField('userId', 'integer')->columnName('user_id')->option('unsigned', true)->build();
            $builder->addIndex(['user_id'], 'user_id');
        }

        if (!$metadata->hasAssociation('sessions')) {
            $metadata->mapOneToMany([
                'fieldName' => 'sessions',
                'mappedBy' => 'token',
                'targetEntity' => $this->sessionClass
            ]);
        }
    }

    protected function mapSession(ClassMetadata $metadata, EntityManager $em) {
        $metadata->setPrimaryTable([
            'name' => 'remember_me__sessions',
            'options' => [
                'collate' => 'utf8mb4_unicode_ci',
                'charset' => 'utf8mb4'
            ]
        ]);

        $metadata->setCustomRepositoryClass($this->sessionRepositoryClass);

        $builder = new ClassMetadataBuilder($metadata);

//        /**
//         * @ORM\Id
//         * @ORM\Column(name="id", type="integer", options={"unsigned"=true}, nullable=false)
//         * @ORM\GeneratedValue(strategy="AUTO")
//         */
//        protected $id;
        if (!$metadata->hasField('id')) {
            $this->mapId($metadata, $em);
        }

//        /**
//         * @ORM\Column(name="identifier", type="string", nullable=false)
//         */
//        protected $value;
        if (!$metadata->hasField('identifier')) {
            $builder->createField('identifier', 'string')->columnName('identifier')->nullable(false)->build();
        }

//        /**
//         * @ORM\Column(name="dateCreated", type="datetime")
//         */
//        protected $dateCreated;
        if (!$metadata->hasField('dateCreated')) {
            $builder->createField('dateCreated', 'datetime')->columnName('date_created')->build();
        }

        if (!$metadata->hasAssociation('token')) {
            $metadata->mapManyToOne([
                'fieldName' => 'token',
                'inversedBy' => 'sessions',
                'joinColumns' => [[
                    'name' => 'token_id',
                    'referencedColumnName' => 'id',
                    'nullable' => false,
                ]],
                'targetEntity' => $this->tokenClass
            ]);
            $builder->addUniqueConstraint(['token_id', 'identifier'], 'token_series');
        }
    }

    protected function mapDeviceAware(ClassMetadata $metadata) {
        $reflection = new \ReflectionClass(DeviceAwareTrait::class);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PROTECTED);

        $builder = new ClassMetadataBuilder($metadata);

        /** @var \ReflectionProperty $property */
        foreach ($properties as $property) {
            $name = $property->getName();
            if (!$metadata->hasField($name)) {
                $builder->createField($name, 'string')->columnName($name)->length(125)->build();
            }
        }
    }

    private function mapId(ClassMetadata $metadata, EntityManager $em) {
        $builder = new ClassMetadataBuilder($metadata);
        $builder->createField('id', 'integer')->makePrimaryKey()->generatedValue()->option('unsigned', true)->build();

        $platform = $em->getConnection()->getDatabasePlatform();

        $idGenType = $metadata->generatorType;
        if (ClassMetadata::GENERATOR_TYPE_AUTO == $idGenType) {
            if ($platform->prefersSequences()) {
                $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_SEQUENCE);
            } elseif ($platform->prefersIdentityColumns()) {
                $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_IDENTITY);
            } else {
                $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_TABLE);
            }
        }

        // Copied this shit over from the DoctrineBehaviors Translatable bundle from KNP
        // Basically, all that needs to be done here is to add IdentityGenerator to $metadata
        // But I'll keep this here just in case anybody uses a different environment

        $class = $metadata;

        // Create & assign an appropriate ID generator instance
        switch ($class->generatorType) {
            case ClassMetadata::GENERATOR_TYPE_IDENTITY:
                // For PostgreSQL IDENTITY (SERIAL) we need a sequence name. It defaults to
                // <table>_<column>_seq in PostgreSQL for SERIAL columns.
                // Not pretty but necessary and the simplest solution that currently works.
                $sequenceName = null;
                $fieldName = $class->identifier ? $class->getSingleIdentifierFieldName() : null;

                if ($platform instanceof Platforms\PostgreSqlPlatform) {
                    $columnName = $class->getSingleIdentifierColumnName();
                    $quoted = isset($class->fieldMappings[$fieldName]['quoted']) || isset($class->table['quoted']);
                    $sequenceName = $class->getTableName().'_'.$columnName.'_seq';
                    $definition = [
                        'sequenceName' => $platform->fixSchemaElementName($sequenceName)
                    ];

                    if ($quoted) {
                        $definition['quoted'] = true;
                    }

                    $sequenceName = $em->getConfiguration()->getQuoteStrategy()->getSequenceName($definition, $class, $platform);
                }

                $generator = ($fieldName && $class->fieldMappings[$fieldName]['type'] === 'bigint')
                    ? new BigIntegerIdentityGenerator($sequenceName)
                    : new IdentityGenerator($sequenceName);

                $class->setIdGenerator($generator);

                break;

            case ClassMetadata::GENERATOR_TYPE_SEQUENCE:
                // If there is no sequence definition yet, create a default definition
                $definition = $class->sequenceGeneratorDefinition;

                if (!$definition) {
                    $fieldName = $class->getSingleIdentifierFieldName();
                    $columnName = $class->getSingleIdentifierColumnName();
                    $quoted = isset($class->fieldMappings[$fieldName]['quoted']) || isset($class->table['quoted']);
                    $sequenceName = $class->getTableName().'_'.$columnName.'_seq';
                    $definition = [
                        'sequenceName' => $platform->fixSchemaElementName($sequenceName),
                        'allocationSize' => 1,
                        'initialValue' => 1,
                    ];

                    if ($quoted) {
                        $definition['quoted'] = true;
                    }

                    $class->setSequenceGeneratorDefinition($definition);
                }

                $sequenceGenerator = new SequenceGenerator(
                    $em->getConfiguration()->getQuoteStrategy()->getSequenceName($definition, $class, $platform),
                    $definition['allocationSize']
                );
                $class->setIdGenerator($sequenceGenerator);
                break;

            case ClassMetadata::GENERATOR_TYPE_NONE:
                $class->setIdGenerator(new AssignedGenerator());
                break;

            case ClassMetadata::GENERATOR_TYPE_UUID:
                $class->setIdGenerator(new UuidGenerator());
                break;

            case ClassMetadata::GENERATOR_TYPE_TABLE:
                throw new ORMException('TableGenerator not yet implemented.');
                break;

            case ClassMetadata::GENERATOR_TYPE_CUSTOM:
                $definition = $class->customGeneratorDefinition;
                if (!class_exists($definition['class'])) {
                    throw new ORMException("Can't instantiate custom generator : ".
                        $definition['class']);
                }
                $class->setIdGenerator(new $definition['class']());
                break;

            default:
                throw new ORMException('Unknown generator type: '.$class->generatorType);
        }
    }
}
