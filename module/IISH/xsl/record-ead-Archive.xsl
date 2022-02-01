<?xml version="1.0" encoding="UTF-8"?>

<!-- For the rendering see SEARCH-163
    We will return a model in json. The PHP will serialize that into an array to the view.
-->

<xsl:stylesheet version="1.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:ead="urn:isbn:1-931666-22-9"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xlink="http://www.w3.org/1999/xlink"
                xsi:schemaLocation="urn:isbn:1-931666-22-9 https://www.loc.gov/ead/ead.xsd"
                xmlns:ext="http://exslt.org/common"
                xmlns:php="http://php.net/xsl"
                exclude-result-prefixes="xsl ead xsi ext php xlink">

  <xsl:output method="xml" omit-xml-declaration="yes" encoding="UTF-8" indent="no"/>

  <xsl:template name="row">
    <xsl:param name="key"/>
    <xsl:param name="value"/>
    <xsl:if test="string-length($value) > 0">
      <tr>
        <th>
          <xsl:call-template name="language">
            <xsl:with-param name="key" select="normalize-space($key)"/>
          </xsl:call-template>
        </th>
        <td>
          <xsl:copy-of select="$value"/>
        </td>
      </tr>
    </xsl:if>
  </xsl:template>

  <xsl:template name="aname">
    <xsl:param name="value"/>
    <xsl:param name="tag"/>
    <a name="{php:function('IISH\XSLT\Import\IISH::generateID', normalize-space($value), $tag)}">
      <xsl:value-of select="$value"/>
    </a>
  </xsl:template>

  <xsl:template name="ahref">
    <xsl:param name="value"/>
    <xsl:param name="tag"/>
    <a href="{concat('#', php:function('IISH\XSLT\Import\IISH::generateID', normalize-space($value), $tag))}">
      <xsl:value-of select="$value"/>
    </a>
  </xsl:template>

  <xsl:template name="language">
    <xsl:param name="key"/>
    <xsl:value-of select="php:function('IISH\XSLT\Import\IISH::translate', normalize-space($key))"/>
  </xsl:template>

  <xsl:template match="ead:corpname|ead:persname|ead:name">
    <xsl:apply-templates select="node()|@*"/>
  </xsl:template>

  <xsl:template match="ead:subarea">
    <xsl:apply-templates select="text()"/>
  </xsl:template>

  <xsl:template match="ead:p">
    <p>
      <xsl:apply-templates select="node()"/>
    </p>
  </xsl:template>

  <xsl:template match="ead:title | ead:emph">
    <xsl:if test="node()">
      <i>
        <xsl:apply-templates select="node()"/>
      </i>
    </xsl:if>
  </xsl:template>

  <xsl:template match="ead:list">
    <ul class="list">
      <xsl:apply-templates select="node()|@*"/>
    </ul>
  </xsl:template>

  <xsl:template match="ead:item">
    <li>
      <xsl:apply-templates select="node()|@*"/>
    </li>
  </xsl:template>

  <xsl:template match="ead:table">
    <table class="ead-table table table-condensed table-striped">
      <xsl:apply-templates select="node()|@*"/>
    </table>
  </xsl:template>

  <xsl:template match="ead:tgroup">
    <xsl:apply-templates select="node()"/>
  </xsl:template>

  <xsl:template match="ead:row">
    <tr>
      <xsl:apply-templates select="node()|@*"/>
    </tr>
  </xsl:template>

  <xsl:template match="ead:entry">
    <xsl:choose>
      <xsl:when test="local-name(../..) = 'thead'">
        <th>
          <xsl:apply-templates select="node()|@*"/>
        </th>
      </xsl:when>
      <xsl:otherwise>
        <td>
          <xsl:apply-templates select="node()|@*"/>
        </td>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <xsl:template match="ead:extref">
    <a href="{@xlink:href}" target="_blank">
      <xsl:if test="@xlink:label">
        <xsl:attribute name="label">
          <xsl:value-of select="@xlink:label"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="@xlink:title">
        <xsl:attribute name="title">
          <xsl:value-of select="@xlink:title"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:apply-templates select="node()"/>
    </a>
  </xsl:template>

  <xsl:template match="ead:lb">
    <br/>
    <xsl:apply-templates select="node()"/>
  </xsl:template>

  <xsl:template match="ead:note">
    <xsl:apply-templates select="node()"/>
  </xsl:template>

  <xsl:template match="ead:daogrp">
    <div class="digital block loading hidden-print"
         data-record="{normalize-space($id)}"
         data-item="{normalize-space(../ead:unitid)}">
      <span class="loading-text">Loading...</span>
    </div>
  </xsl:template>

  <!-- Catch all -->
  <xsl:template match="ead:*">
    <xsl:element name="{local-name()}">
      <xsl:apply-templates select="node()"/>
    </xsl:element>
  </xsl:template>

</xsl:stylesheet>

