# Overview

<p>This module provides the capability to search Freedmen's Bureau Records
in EDAN on a Drupal 8 website.</p>

# Prerequisites
This module requires the EDAN Drupal 8 module and EDAN credentials. The EDAN Drupal 8 module can be found [here](https://github.com/Smithsonian/d8-edan-module).

<p><b>NOTE:</b> All EDAN FB records are currently in EDAN Dev, which requires an SI VPN connection.</p>

# Installation
The module can be installed either by dropping the unzipped module in the appropriate module directory of your Drupal 8 site or by uploading the zip through the Drupal admin interface.

# Configuration
To configure the module settings, navigate to */admin/config/fb_search/settings*.

## Search Settings
The search settings section contains boost values for EDAN searches. Boost values are numeric values that determine the weight applied to each facet field (first name, last name, etc.) in the search.

## Display Settings
The display settings section contains fields that determine how Freedmen's Bureau records are displayed.

# How to use

## Landing Page
The landing page provides a simple keyword search of the Freedmen's Bureau EDAN records. You can find the landing page at */fb-landing-page*.

The landing page search redirects to the list search page.

## List Search Page
The list search page provides a more comprehensive search with filter fields to narrow down search results. You can find the list search page at */fb-search*.

## Record Display Page
Individual records can be viewed by navigation to */fb-object/\<record id\>*. Various record fields will be displayed on the page.
