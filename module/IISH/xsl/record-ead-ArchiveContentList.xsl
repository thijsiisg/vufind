<?xml version="1.0" encoding="UTF-8"?>

<!-- For the rendering see SEARCH-163
    We will return a model in json. The PHP will serialize that into an array to the view.
-->

<xsl:stylesheet version="1.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:ead="urn:isbn:1-931666-22-9"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xsi:schemaLocation="urn:isbn:1-931666-22-9 http://www.loc.gov/ead/ead.xsd"
                exclude-result-prefixes="xsl ead xsi">


  <xsl:import href="record-ead-Archive.xsl"/>
  <xsl:output method="xml" omit-xml-declaration="yes" encoding="UTF-8" indent="no"/>
  <xsl:strip-space elements="*"/>

  <xsl:param name="id"/>
  <xsl:param name="action"/>
  <xsl:param name="baseUrl"/>
  <xsl:param name="lang"/>
  <xsl:param name="title"/>

  <xsl:template match="/">
    <xsl:apply-templates select="//ead:ead"/>
  </xsl:template>

  <xsl:template match="ead:ead">
    <div id="arch" class="holdings-container with-children archive"
         data-show-reservation="true" data-show-reproduction="false">
      <xsl:attribute name="data-label">
        <xsl:value-of select="$title"/>
      </xsl:attribute>
      <xsl:attribute name="data-pid">
        <xsl:value-of select="substring(ead:eadheader/ead:eadid/@identifier, 5)"/>
      </xsl:attribute>

      <xsl:for-each select="//ead:dsc[1]/ead:c01">
        <xsl:call-template name="cxx"/>
      </xsl:for-each>
    </div>
  </xsl:template>

  <xsl:template name="cxx">
    <xsl:variable name="value">
      <xsl:apply-templates select="." mode="l"/>
    </xsl:variable>

    <xsl:if test="$value">
      <xsl:copy-of select="$value"/>

      <xsl:variable name="group">
        <xsl:for-each select="*[starts-with(local-name(), 'c')]">
          <xsl:call-template name="cxx"/>
        </xsl:for-each>
      </xsl:variable>

      <xsl:choose>
        <xsl:when test="@level = 'series' or @level = 'subseries'">
          <div class="{@level}-group">
            <xsl:copy-of select="$group"/>
          </div>
        </xsl:when>

        <xsl:otherwise>
          <xsl:copy-of select="$group"/>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:if>
  </xsl:template>

  <xsl:template name="delivery">
    <xsl:if test="count(.//ead:did/ead:unitid) = 1 and not(./ead:did/ead:daogrp)">
      <xsl:variable name="child">
        <xsl:value-of select="ead:did/ead:unitid"/>
      </xsl:variable>

      <xsl:variable name="restriction">
        <xsl:value-of select="ead:accessrestrict/@type"/>
      </xsl:variable>

      <xsl:if test="$child != '' and $child != '-' and $restriction != 'closed'">
        <div class="holding">
          <div class="state hidden-print">
            <xsl:attribute name="data-child">
              <xsl:value-of select="$child"/>
            </xsl:attribute>
            <wbr/>
          </div>
        </div>
      </xsl:if>
    </xsl:if>
  </xsl:template>

  <xsl:template
      match="ead:c01|ead:c02|ead:c03|ead:c04|ead:c05|ead:c06|ead:c07|ead:c08|ead:c09|ead:c10|ead:c11|ead:c12"
      mode="l">
    <xsl:choose>
      <xsl:when test="@level = 'series' or @level = 'subseries'">
        <xsl:apply-templates select="*[not(starts-with(local-name(),'c'))]"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:variable name="t">
          <xsl:apply-templates select="ead:did/*[not(local-name() = 'unitid' or local-name() = 'daogrp')]"/>
        </xsl:variable>

        <div class="k{@level}">
          <a class="b" name="{ead:did/ead:unitid}">
            <xsl:if test="ead:did/ead:unitid != '' and ead:did/ead:unitid != '-'">
              <xsl:apply-templates select="ead:did/ead:unitid"/>.
            </xsl:if>
          </a>
        </div>

        <xsl:if test="string-length($t)>1">
          <div class="v{@level}">
            <xsl:copy-of select="$t"/>

            <xsl:apply-templates select="ead:accessrestrict|ead:scopecontent|ead:odd"/>

            <xsl:call-template name="delivery"/>
            <xsl:apply-templates select="ead:did/ead:daogrp"/>
          </div>
        </xsl:if>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <xsl:template match="ead:did">
    <xsl:apply-templates/>
  </xsl:template>

  <xsl:template match="ead:unitid">
    <xsl:if test="../../@level = 'file' or ../../@level = 'item'">
      <xsl:apply-templates/>
    </xsl:if>
  </xsl:template>

  <xsl:template match="ead:unittitle">
    <xsl:choose>
      <xsl:when test="../../@level = 'file' or ../../@level = 'item'">
        <xsl:apply-templates/>
        <xsl:if test="following-sibling::*[1][not(local-name() = 'physdesc')]">
          <br/>
        </xsl:if>
      </xsl:when>

      <xsl:when test="../../@level = 'series'">
        <h2>
          <xsl:call-template name="aname">
            <xsl:with-param name="value">
              <xsl:apply-templates/>
            </xsl:with-param>
            <xsl:with-param name="tag" select="../../../ead:did/ead:unittitle/text()"/>
          </xsl:call-template>
        </h2>
      </xsl:when>

      <xsl:when test="../../@level = 'subseries'">
        <xsl:choose>
          <xsl:when
              test="ancestor::ead:c04 | ancestor::ead:c05 | ancestor::ead:c06 | ancestor::ead:c07
                             | ancestor::ead:c08 | ancestor::ead:c09 | ancestor::ead:c09 | ancestor::ead:c10
                              | ancestor::ead:c11 | ancestor::ead:c12">
            <h5>
              <xsl:call-template name="aname">
                <xsl:with-param name="value">
                  <xsl:apply-templates/>
                </xsl:with-param>
                <xsl:with-param name="tag" select="../../../ead:did/ead:unittitle/text()"/>
              </xsl:call-template>
            </h5>
          </xsl:when>

          <xsl:when test="ancestor::ead:c02">
            <h3>
              <xsl:call-template name="aname">
                <xsl:with-param name="value">
                  <xsl:apply-templates/>
                </xsl:with-param>
                <xsl:with-param name="tag" select="../../../ead:did/ead:unittitle/text()"/>
              </xsl:call-template>
            </h3>
          </xsl:when>

          <xsl:when test="ancestor::ead:c03">
            <h4>
              <xsl:call-template name="aname">
                <xsl:with-param name="value">
                  <xsl:apply-templates/>
                </xsl:with-param>
                <xsl:with-param name="tag" select="../../../ead:did/ead:unittitle/text()"/>
              </xsl:call-template>
            </h4>
          </xsl:when>
        </xsl:choose>
      </xsl:when>
    </xsl:choose>
  </xsl:template>

  <xsl:template match="ead:unitdate">
    <xsl:value-of select="normalize-space(text())"/>
  </xsl:template>

  <xsl:template match="ead:physdesc">
    <xsl:text> </xsl:text>
    <span class="physdesc">
      <xsl:apply-templates/>
    </span>
    <br/>
  </xsl:template>

  <xsl:template match="ead:note">
    <div class="note">
      <xsl:apply-templates/>
    </div>
  </xsl:template>

  <xsl:template match="ead:head">
    <h5>
      <xsl:call-template name="aname">
        <xsl:with-param name="value">
          <xsl:value-of select="normalize-space(text())"/>
        </xsl:with-param>
        <xsl:with-param name="tag" select="'1'"/>
      </xsl:call-template>
    </h5>
  </xsl:template>

  <xsl:template match="ead:accessrestrict">
    <div class="warning">
      <xsl:apply-templates/>
    </div>
  </xsl:template>

  <xsl:template match="ead:extent">
    <xsl:value-of select="normalize-space(text())"/>
  </xsl:template>

  <xsl:template match="ead:scopecontent">
    <div class="scopecontent">
      <xsl:apply-templates/>
    </div>
  </xsl:template>

  <xsl:template match="ead:odd">
    <div class="odd">
      <xsl:apply-templates/>
    </div>
  </xsl:template>
</xsl:stylesheet>

