<?php

namespace App\Enums;

enum Industries: string
{
    case Agriculture = 'AGRICULTURE';
    case Fishing = 'FISHING';
    case Mining_and_quarrying = 'MINING AND QUARRYING';
    case Manufacturing = 'MANUFACTURING';
    case Electricity_gas_and_water_supply = 'ELECTRICITY, GAS, AND WATER SUPPLY';
    case Construction = 'CONSTRUCTION';
    case Wholesale_and_retail_trade = 'WHOLESALE AND RETAIL TRADE';
    case Hotels_and_restaurants = 'HOTELS AND RESTAURANTS';
    case Transport_storage_and_communication = 'TRANSPORT, STORAGE, AND COMMUNICATION';
    case Financial_intermediation = 'FINANCIAL INTERMEDIATION';
    case Real_estate_renting_and_business_activities = 'REAL ESTATE, RENTING, AND BUSINESS ACTIVITIES';
    case Public_administration_and_defense = 'PUBLIC ADMINISTRATION AND DEFENSE';
    case Education = 'EDUCATION';
    case Health_and_social_worker = 'HEALTH AND SOCIAL WORKER';
    case Other_community_and_personal_service_activities = 'OTHER COMMUNITY, SOCIAL AND PERSONAL SERVICE ACTIVITIES';
    case Activities_of_private_households_as_employers = 'ACTIVITIES OF PRIVATE HOUSEHOLDS AS EMPLOYERS...';
    case Extra_territorial_organizations_and_bodies = 'EXTRA-TERRITORIAL ORGANIZATIONS AND BODIES';

    public function Label(): string
    {
        return match ($this) {
            self::Agriculture => 'Agriculture',
            self::Fishing => 'Fishing',
            self::Mining_and_quarrying => 'Mining and Quarrying',
            self::Manufacturing => 'Manufacturing',
            self::Electricity_gas_and_water_supply => 'Electricity, Gas, and Water Supply',
            self::Construction => 'Construction',
            self::Wholesale_and_retail_trade => 'Wholesale and Retail Trade',
            self::Hotels_and_restaurants => 'Hotels and Restaurants',
            self::Transport_storage_and_communication => 'Transport, Storage, and Communication',
            self::Financial_intermediation => 'Financial Intermediation',
            self::Real_estate_renting_and_business_activities => 'Real Estate, Renting, and Business Activities',
            self::Public_administration_and_defense => 'Public Administration and Defense',
            self::Education => 'Education',
            self::Health_and_social_worker => 'Health and Social Worker',
            self::Other_community_and_personal_service_activities => 'Other Community, Social and Personal Service Activities',
            self::Activities_of_private_households_as_employers => 'Activities of Private Households as Employers...',
            self::Extra_territorial_organizations_and_bodies => 'Extra-Territorial Organizations and Bodies',
        };
    }

    public static function FillSelect() :array {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->Label();
        }
        return $options;
    }

    public static function Array() :array{
        return array_column(self::cases(), 'value');
    }
}
