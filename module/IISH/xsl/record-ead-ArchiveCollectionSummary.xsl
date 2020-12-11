<?xml version="1.0" encoding="UTF-8"?>

<!-- For the rendering see SEARCH-163
    We will return a model in json. The PHP will serialize that into an array to the view.
-->

<xsl:stylesheet version="1.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:ead="urn:isbn:1-931666-22-9"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:xs="http://www.w3.org/1999/XSL/Transform"
                xsi:schemaLocation="urn:isbn:1-931666-22-9 http://www.loc.gov/ead/ead.xsd"
                xmlns:php="http://php.net/xsl"
                exclude-result-prefixes="xsl ead xsi php xlink">

  <xsl:import href="record-ead-Archive.xsl"/>
  <xsl:output method="xml" omit-xml-declaration="yes" encoding="UTF-8" indent="no"/>
  <xsl:strip-space elements="*"/>

  <xsl:param name="action"/>
  <xsl:param name="baseUrl"/>
  <xsl:param name="lang"/>
  <xsl:param name="title"/>
  <xsl:param name="isInternal"/>

  <xsl:variable name="digital_items"
                select="count(//ead:daogrp[starts-with(ead:daoloc/@xlink:href, 'http://hdl.handle.net/10622/') or starts-with(ead:daoloc/@xlink:href, 'https://hdl.handle.net/10622/')])"/>

  <xsl:template match="/">
    <xsl:apply-templates select="//ead:ead"/>
  </xsl:template>

  <xsl:template match="ead:ead">
    <div class="row">
      <xsl:if test="$digital_items>0">
        <div id="teaser" class="col-sm-3 col-sm-push-9">
          <img class="center-block" src=""/>
          <p>
            <xsl:call-template name="language">
              <xsl:with-param name="key">ArchiveCollectionSummary.image</xsl:with-param>
            </xsl:call-template>
          </p>
        </div>
      </xsl:if>

      <xsl:variable name="arch_class">
        <xsl:choose>
            <xsl:when test="$digital_items>0">
              <xsl:text>col-sm-9 col-sm-pull-3</xsl:text>
            </xsl:when>
            <xsl:otherwise>
              <xsl:text>col-sm-12</xsl:text>
            </xsl:otherwise>
        </xsl:choose>
      </xsl:variable>

      <div id="arch" class="{$arch_class}">
        <table class="table table-striped">
          <xsl:call-template name="reproduction"/>
          <xsl:call-template name="creator"/>
          <xsl:call-template name="secondcreator"/>
          <xsl:call-template name="abstract"/>
          <xsl:call-template name="period"/>
          <xsl:call-template name="extent"/>
          <xsl:call-template name="access"/>
          <xsl:call-template name="digitalform"/>
          <xsl:call-template name="langmaterial"/>
          <xsl:call-template name="collectionid"/>
          <xsl:call-template name="repository"/>
          <xsl:call-template name="pid"/>
        </table>
      </div>
    </div>

    <xsl:if test="$digital_items>0">
      <script type="text/javascript">
        (function() {
        var urls=[
        <xsl:for-each select="//ead:daogrp/ead:daoloc[@xlink:label='thumbnail'][1]/@xlink:href">
          '<xsl:value-of select="."/>'
          <xsl:if test="not(position()=last())">,</xsl:if>
        </xsl:for-each>
        ];
        function swap() {
        $('#teaser img').attr('src', urls[Math.round(Math.random() * urls.length)].replace('http:', 'https:'));
        }
        swap();
        $('#teaser img').click(function(){
        var src = this.src;
        document.location.href = src.substring(0, src.length-6) + 'catalog';
        });
        setInterval( swap, 5000 ) ;
        })();
      </script>
    </xsl:if>

  </xsl:template>

  <xsl:template name="reproduction">
    <tr>
      <th> </th>
      <td>
        <xsl:variable name="colid" select="ead:archdesc/ead:did/ead:unitid"/>
        <xsl:variable name="identifier" select="ead:eadheader/ead:eadid/@identifier"/>
        <div>
          <xsl:attribute name="class">
            <xsl:choose>
              <xsl:when test="$digital_items>0">
                holdings-container no-children archive online-content-available
              </xsl:when>
              <xsl:otherwise>
                holdings-container no-children archive
              </xsl:otherwise>
            </xsl:choose>
          </xsl:attribute>
          <div class="holding">
            <!-- Override concerning corona virus -->
<!--            <xsl:if test="ead:archdesc/ead:dsc/ead:c01">-->
            <xsl:if test="ead:archdesc/ead:dsc/ead:c01 and $isInternal">
              <a class="deliveryReserveButton reservationBtn" href="{concat($baseUrl, '/', 'ArchiveContentList')}">
                <xsl:call-template name="language">
                  <xsl:with-param name="key" select="'request_reservation'"/>
                </xsl:call-template>
              </a>
            </xsl:if>

            <div class="state hidden-print">
              <xsl:attribute name="data-label">
                <xsl:value-of select="$title"/>
              </xsl:attribute>

              <xsl:attribute name="data-pid">
                <xsl:value-of select="substring($identifier, 5)"/>
              </xsl:attribute>

              <xsl:attribute name="data-signature">
                <xsl:value-of select="$colid"/>
              </xsl:attribute>

              <!-- Override concerning corona virus -->
<!--              <xsl:attribute name="data-show-reservation">-->
<!--                <xsl:choose>-->
<!--                  <xsl:when test="ead:archdesc/ead:dsc/ead:c01">false</xsl:when>-->
<!--                  <xsl:otherwise>true</xsl:otherwise>-->
<!--                </xsl:choose>-->
<!--              </xsl:attribute>-->

              <xsl:attribute name="data-show-reservation">
                <xsl:choose>
                  <xsl:when test="ead:archdesc/ead:dsc/ead:c01">false</xsl:when>
                  <xsl:when test="$isInternal">true</xsl:when>
                  <xsl:otherwise>false</xsl:otherwise>
                </xsl:choose>
              </xsl:attribute>

              <xsl:attribute name="data-show-reproduction">true</xsl:attribute>

              <xsl:variable name="access-restrict" select="ead:archdesc/ead:descgrp[@type='access_and_use']/ead:accessrestrict"/>
              <xsl:variable name="top-access" select="normalize-space($access-restrict/ead:p[1]/text())"/>
              <xsl:variable name="restricted-items" select="ead:archdesc/ead:dsc//ead:accessrestrict[@type='restricted']"/>

              <xsl:attribute name="data-show-permission">
                <xsl:choose>
                  <xsl:when test="$colid = 'ARCH00293' or $colid = 'ARCH00393'">false</xsl:when>
                  <xsl:when test="(not($access-restrict/@type) or $access-restrict/@type != 'part') and ($top-access = 'Restricted' or $top-access = 'Beperkt')">true</xsl:when>
                  <xsl:when test="$access-restrict/@type = 'part' and $restricted-items">true</xsl:when>
                  <xsl:otherwise>false</xsl:otherwise>
                </xsl:choose>
              </xsl:attribute>
            </div>
          </div>
        </div>
      </td>
    </tr>
  </xsl:template>

  <xsl:template name="creator">
    <xsl:variable name="value">
      <xsl:for-each select="ead:archdesc/ead:did/ead:origination[@label='Creator' or @label='creator']/*">
        <li>
          <xsl:apply-templates/>
        </li>
      </xsl:for-each>
    </xsl:variable>

    <xsl:if test="string-length($value) > 0">
      <xsl:call-template name="row">
        <xsl:with-param name="key" select="'ArchiveCollectionSummary.creator.first'"/>
        <xsl:with-param name="value">
          <ul>
            <xsl:copy-of select="$value"/>
          </ul>
        </xsl:with-param>
      </xsl:call-template>
    </xsl:if>
  </xsl:template>

  <xsl:template name="secondcreator">
    <xsl:variable name="value">
      <xsl:for-each select="ead:archdesc/ead:did/ead:origination[not(@label='Creator' or @label='creator')]/*">
        <li>
          <xsl:apply-templates/>
        </li>
      </xsl:for-each>
    </xsl:variable>
    <xsl:if test="string-length($value) > 0">
      <xsl:call-template name="row">
        <xsl:with-param name="key" select="'ArchiveCollectionSummary.creator.second'"/>
        <xsl:with-param name="value">
          <ul>
            <xsl:copy-of select="$value"/>
          </ul>
        </xsl:with-param>
      </xsl:call-template>
    </xsl:if>
  </xsl:template>

  <xsl:template name="abstract">
    <xsl:variable name="more">
      <xsl:value-of
          select="php:function('IISH\XSLT\Import\IISH::truncate' , string(ead:archdesc/ead:descgrp[@type='content_and_structure']/ead:scopecontent/ead:p[1]), 300)"/>
      <a href="{concat($baseUrl, '/', 'ArchiveContentAndStructure')}">
        <br/>
        <xsl:call-template name="language">
          <xsl:with-param name="key" select="'ArchiveCollectionSummary.abstract.more'"/>
        </xsl:call-template>
      </a>
    </xsl:variable>
    <xsl:call-template name="row">
      <xsl:with-param name="key" select="'ArchiveCollectionSummary.abstract'"/>
      <xsl:with-param name="value" select="$more"/>
    </xsl:call-template>
  </xsl:template>

  <xsl:template name="period">
    <xsl:for-each select="ead:archdesc/ead:did/ead:unitdate">
      <xsl:variable name="key">
        <xsl:choose>
          <xsl:when test="position()=1">
            <xsl:value-of
                select="'ArchiveCollectionSummary.period'"/>
          </xsl:when>
          <xsl:otherwise/>
        </xsl:choose>
      </xsl:variable>
      <xsl:call-template name="row">
        <xsl:with-param name="key" select="$key"/>
        <xsl:with-param name="value">
          <xsl:value-of select="text()"/>
          (
          <xsl:call-template name="language">
            <xsl:with-param name="key"
                            select="concat('ArchiveCollectionSummary.period', '.', @type)"/>
          </xsl:call-template>
          )
        </xsl:with-param>
      </xsl:call-template>
    </xsl:for-each>
  </xsl:template>

  <xsl:template name="extent">
    <xsl:variable name="value">
      <xsl:for-each select="ead:archdesc/ead:did/ead:physdesc/ead:extent">
        <li>
          <xsl:apply-templates/>
        </li>
      </xsl:for-each>
    </xsl:variable>
    <xsl:if test="string-length($value) > 0">
      <xsl:call-template name="row">
        <xsl:with-param name="key" select="'ArchiveCollectionSummary.extent'"/>
        <xsl:with-param name="value">
          <ul>
            <xsl:copy-of select="$value"/>
          </ul>
        </xsl:with-param>
      </xsl:call-template>
    </xsl:if>
  </xsl:template>

  <xsl:template name="access">
    <xsl:call-template name="row">
      <xsl:with-param name="key" select="'ArchiveCollectionSummary.access'"/>
      <xsl:with-param name="value">
        <a href="{concat($baseUrl, '/', 'ArchiveAccessAndUse')}">
          <xsl:value-of
              select="ead:archdesc/ead:descgrp[@type='access_and_use']/ead:accessrestrict/ead:p[1]/text()"/>
        </a>
      </xsl:with-param>
    </xsl:call-template>
  </xsl:template>

  <xsl:template name="digitalform">
    <xsl:if test="$digital_items>0">
      <xsl:variable name="value">
        <xsl:value-of select="$digital_items"/>
        <xsl:text>&#032;</xsl:text>
        <xsl:call-template name="language">
          <xsl:with-param name="key" select="'ArchiveCollectionSummary.digitalform.items'"/>
        </xsl:call-template>
      </xsl:variable>
      <xsl:call-template name="row">
        <xsl:with-param name="key" select="'ArchiveCollectionSummary.digitalform'"/>
        <xsl:with-param name="value" select="$value"/>
      </xsl:call-template>
    </xsl:if>
  </xsl:template>

  <xsl:template name="langmaterial">
    <xsl:variable name="value">
      <xsl:for-each select="ead:archdesc/ead:did/ead:langmaterial/ead:language">
        <li>
          <xsl:apply-templates/>
        </li>
      </xsl:for-each>
    </xsl:variable>
    <xsl:if test="string-length($value) > 0">
      <xsl:call-template name="row">
        <xsl:with-param name="key" select="'ArchiveCollectionSummary.langmaterial'"/>
        <xsl:with-param name="value">
          <ul>
            <xsl:copy-of select="$value"/>
          </ul>
        </xsl:with-param>
      </xsl:call-template>
    </xsl:if>
  </xsl:template>

  <xsl:template name="collectionid">
    <xsl:call-template name="row">
      <xsl:with-param name="key" select="'ArchiveCollectionSummary.collectionID'"/>
      <xsl:with-param name="value">
        <xsl:value-of select="ead:archdesc/ead:did/ead:unitid"/>
      </xsl:with-param>
    </xsl:call-template>
  </xsl:template>

  <xsl:template name="repository">
    <xsl:variable name="repository" select="ead:archdesc/ead:did/ead:repository/ead:corpname"/>
    <xsl:if test="$repository">
      <xsl:call-template name="row">
        <xsl:with-param name="key" select="'ArchiveCollectionSummary.repository'"/>
        <xsl:with-param name="value" select="$repository/text()"/>
      </xsl:call-template>
    </xsl:if>
  </xsl:template>

  <xsl:template name="pid">
    <xsl:variable name="handle" select="normalize-space( ead:eadheader/ead:eadid)"/>
    <xsl:call-template name="row">
      <xsl:with-param name="key" select="'ArchiveCollectionSummary.pid'"/>
      <xsl:with-param name="value">
        <a href="{$handle}" target="_blank">
          <xsl:value-of select="$handle"/>
        </a>
      </xsl:with-param>
    </xsl:call-template>
  </xsl:template>

</xsl:stylesheet>

