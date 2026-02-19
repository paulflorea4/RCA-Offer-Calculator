📄 RCA Insurance Offer Integration – Laravel

A Laravel-based application for generating, comparing, and issuing RCA insurance offers through third-party insurer APIs.

The project integrates multiple insurance providers via a unified service layer, allowing users to:

  -Retrieve vehicle data from external APIs
  
  -Generate RCA offers from multiple insurers simultaneously

  -Normalize and display offer results

  -Download offer documents (PDF)

  -Issue policies based on selected offers

The architecture focuses on clean separation between controllers, services, and payload builders to simplify provider integrations and maintain scalability.


🚀 Features

  -Multi-provider RCA offer generation

  -External API integration using Laravel HTTP client

  -Payload builder for policyholder and vehicle data

  -Dynamic offer filtering and normalization

  -PDF document extraction from Base64 API responses

  -Policy issuance workflow

  -Blade-based UI

🧱 Tech Stack
  -Laravel
  
  -PHP
  
  -REST API integrations
  
  -Blade templates
  

🏗 Architecture Highlights
  -Service layer for insurer API communication
  
  -Centralized payload builder

  -Error-safe API handling
  
  -Provider response normalization
