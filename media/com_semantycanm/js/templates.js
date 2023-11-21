// templates.js

// Common spacer table attributes
const spacerWidth = '100%'
const spacerCellSpacing = '0'
const spacerCellPadding = '0'
const spacerBorder = '0'
const spacerHeight = '10'
const titleFontSize = '25px'
const titleFontFamily = 'Arial, Helvetica, sans-serif'
// const spacerFontSize = "10px";
// const spacerLineHeight = "10px";

window.templates = {

  // Article #1

  0: function (title, url, intro, category) {
    return `
    <!-- Category Container -->
    <table width="100%" cellspacing="0" cellpadding="0" border="0" bgcolor="#152E52">
    <tr>
    <!-- Left padding -->
    <td width="3" bgcolor="#152E52">&nbsp;</td>
    <!-- Main content with top and bottom padding -->
    <td valign="middle" bgcolor="#152E52">
    <table width="100%" cellspacing="0" cellpadding="5" border="0">
    <tr>
    <td bgcolor="#152E52" style="color: #FFFFFF; font-size: 22px;">
    ${category}
    </td>
    </tr>
    </table>
    </td>
    <!-- Right padding - assuming no padding -->
    <td bgcolor="#152E52">&nbsp;</td>
    </tr>
    </table>
    <!-- Spacer for margin-top -->
    <table width="${spacerWidth}" cellspacing="${spacerCellSpacing}" cellpadding="${spacerCellPadding}" border="${spacerBorder}">
    <tr>
    <td height="${spacerHeight}"></td>
    </tr>
    </table>
    <!-- Title Container -->
    <table width="100%" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF">
    <tr>
    <td>
    <a href="${url}" style="font-size: ${titleFontSize}; color: #152E52; text-decoration: none; font-family: ${titleFontFamily};">${title}</a>
    </td>
    </tr>
    </table>
    <!-- Spacer for margin-top -->
    <table width="${spacerWidth}" cellspacing="${spacerCellSpacing}" cellpadding="${spacerCellPadding}" border="${spacerBorder}">
    <tr>
    <td height="${spacerHeight}"></td>
    </tr>
    </table>
    <!-- Intro Container -->
    <table width="100%" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF">
    <tr>
    <td>
    ${intro}
    </td>
    </tr>
    </table>
    `;
  },

  // Article #2

  1: function (title, url, intro, category) {
    return `
    <!-- Spacer for margin-top -->
    <table width="${spacerWidth}" cellspacing="${spacerCellSpacing}" cellpadding="${spacerCellPadding}" border="${spacerBorder}">
    <tr>
    <td height="${spacerHeight}">&nbsp;</td>
    </tr>
    </table>
    <!-- Category Container -->
    <table width="100%" cellspacing="0" cellpadding="0" border="0" bgcolor="#AEC127">
    <tr>
    <!-- Left padding -->
    <td width="3" bgcolor="#AEC127">&nbsp;</td>
    <!-- Main content with top and bottom padding -->
    <td valign="middle" bgcolor="#AEC127">
    <table width="100%" cellspacing="0" cellpadding="5" border="0">
    <tr>
    <td bgcolor="#AEC127" style="color: #FFFFFF; font-size: 24px;">
    ${category}
    </td>
    </tr>
    </table>
    </td>
    <!-- Right padding - assuming no padding -->
    <td bgcolor="#AEC127">&nbsp;</td>
    </tr>
    </table>
    <!-- Spacer for margin-top -->
    <table width="${spacerWidth}" cellspacing="${spacerCellSpacing}" cellpadding="${spacerCellPadding}" border="${spacerBorder}">
    <tr>
    <td height="${spacerHeight}"></td>
    </tr>
    </table>
    <!-- Title Container -->
    <table width="100%" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF">
    <tr>
    <td>
    <a href="${url}" style="font-size: ${titleFontSize}; color: #AEC127; text-decoration: none; font-family: ${titleFontFamily};">${title}</a>
    </td>
    </tr>
    </table>
    <!-- Spacer for margin-top -->
    <table width="${spacerWidth}" cellspacing="${spacerCellSpacing}" cellpadding="${spacerCellPadding}" border="${spacerBorder}">
    <tr>
    <td height="${spacerHeight}"></td>
    </tr>
    </table>
    <!-- Intro Container -->
    <table width="100%" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF">
    <tr>
    <td>
    ${intro}
    </td>
    </tr>
    </table>
    `;
  },

  // Article #3

  2: function (title, url, intro, category) {
    return `
    <!-- Spacer for margin-top -->
    <table width="${spacerWidth}" cellspacing="${spacerCellSpacing}" cellpadding="${spacerCellPadding}" border="${spacerBorder}">
    <tr>
    <td height="${spacerHeight}">&nbsp;</td>
    </tr>
    </table>
    <!-- Category Container -->
    <table width="100%" cellspacing="0" cellpadding="0" border="0" bgcolor="#5CA550">
    <tr>
    <!-- Left padding -->
    <td width="3" bgcolor="#5CA550">&nbsp;</td>
    <!-- Main content with top and bottom padding -->
    <td valign="middle" bgcolor="#5CA550">
    <table width="100%" cellspacing="0" cellpadding="5" border="0">
    <tr>
    <td bgcolor="#5CA550" style="color: #FFFFFF; font-size: 24px;">
    ${category}
    </td>
    </tr>
    </table>
    </td>
    <!-- Right padding - assuming no padding -->
    <td bgcolor="#5CA550">&nbsp;</td>
    </tr>
    </table>
    <!-- Spacer for margin-top -->
    <table width="${spacerWidth}" cellspacing="${spacerCellSpacing}" cellpadding="${spacerCellPadding}" border="${spacerBorder}">
    <tr>
    <td height="${spacerHeight}"></td>
    </tr>
    </table>
    <!-- Title Container -->
    <table width="100%" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF">
    <tr>
    <td>
    <a href="${url}" style="font-size: ${titleFontSize}; color: #5CA550; text-decoration: none; font-family: ${titleFontFamily};">${title}</a>
    </td>
    </tr>
    </table>
    <!-- Spacer for margin-top -->
    <table width="${spacerWidth}" cellspacing="${spacerCellSpacing}" cellpadding="${spacerCellPadding}" border="${spacerBorder}">
    <tr>
    <td height="${spacerHeight}"></td>
    </tr>
    </table>
    <!-- Intro Container -->
    <table width="100%" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF">
    <tr>
    <td>
    ${intro}
    </td>
    </tr>
    </table>
    `;
  },

  // Article #4

  3: function(title, url, intro, category) {
    return `
    <!-- Spacer for margin-top -->
    <table width="${spacerWidth}" cellspacing="${spacerCellSpacing}" cellpadding="${spacerCellPadding}" border="${spacerBorder}">
    <tr>
    <td height="${spacerHeight}">&nbsp;</td>
    </tr>
    </table>
    <!-- Category Container -->
    <table width="100%" cellspacing="0" cellpadding="0" border="0" bgcolor="#DF5F5A">
    <tr>
    <!-- Left padding -->
    <td width="3" bgcolor="#DF5F5A">&nbsp;</td>
    <!-- Main content with top and bottom padding -->
    <td valign="middle" bgcolor="#DF5F5A">
    <table width="100%" cellspacing="0" cellpadding="5" border="0">
    <tr>
    <td bgcolor="#DF5F5A" style="color: #FFFFFF; font-size: 24px;">
    ${category}
    </td>
    </tr>
    </table>
    </td>
    <!-- Right padding - assuming no padding -->
    <td bgcolor="#DF5F5A">&nbsp;</td>
    </tr>
    </table>
    <!-- Spacer for margin-top -->
    <table width="${spacerWidth}" cellspacing="${spacerCellSpacing}" cellpadding="${spacerCellPadding}" border="${spacerBorder}">
    <tr>
    <td height="${spacerHeight}"></td>
    </tr>
    </table>
    <!-- Title Container -->
    <table width="100%" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF">
    <tr>
    <td>
    <a href="${url}" style="font-size: ${titleFontSize}; color: #DF5F5A; text-decoration: none;font-family: ${titleFontFamily};">${title}</a>
    </td>
    </tr>
    </table>
    <!-- Spacer for margin-top -->
    <table width="${spacerWidth}" cellspacing="${spacerCellSpacing}" cellpadding="${spacerCellPadding}" border="${spacerBorder}">
    <tr>
    <td height="${spacerHeight}"></td>
    </tr>
    </table>
    <!-- Intro Container -->
    <table width="100%" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF">
    <tr>
    <td>
    ${intro}
    </td>
    </tr>
    </table>
    `;
  },

  //Aericlw #5

  4: function(title, url, intro, category) {
    return `
    <!-- Spacer for margin-top -->
    <table width="${spacerWidth}" cellspacing="${spacerCellSpacing}" cellpadding="${spacerCellPadding}" border="${spacerBorder}">
    <tr>
    <td height="${spacerHeight}">&nbsp;</td>
    </tr>
    </table>

    <!-- Category Container -->
    <table width="100%" cellspacing="0" cellpadding="0" border="0" bgcolor="#F9AE4F">
    <tr>
    <!-- Left padding -->
    <td width="3" bgcolor="#F9AE4F">&nbsp;</td>

    <!-- Main content with top and bottom padding -->
    <td valign="middle" bgcolor="#F9AE4F">
    <table width="100%" cellspacing="0" cellpadding="5" border="0">
    <tr>
    <td bgcolor="#F9AE4F" style="color: #FFFFFF; font-size: 24px;">
    ${category}
    </td>
    </tr>
    </table>
    </td>

    <!-- Right padding - assuming no padding -->
    <td bgcolor="#F9AE4F">&nbsp;</td>
    </tr>
    </table>

    <!-- Spacer for margin-top -->
    <table width="${spacerWidth}" cellspacing="${spacerCellSpacing}" cellpadding="${spacerCellPadding}" border="${spacerBorder}">
    <tr>
    <td height="${spacerHeight}"></td>
    </tr>
    </table>

    <!-- Title Container -->
    <table width="100%" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF">
    <tr>
    <td>
    <a href="${url}" style="font-size: ${titleFontSize}; color: #F9AE4F; text-decoration: none; font-family: ${titleFontFamily};">${title}</a>
    </td>
    </tr>
    </table>
    <!-- Spacer for margin-top -->
    <table width="${spacerWidth}" cellspacing="${spacerCellSpacing}" cellpadding="${spacerCellPadding}" border="${spacerBorder}">
    <tr>
    <td height="${spacerHeight}"></td>
    </tr>
    </table>
    <!-- Intro Container -->
    <table width="100%" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF">
    <tr>
    <td>
    ${intro}
    </td>
    </tr>
    </table>`;
  },


  // Link #1

  5: function(title, url, intro, category) {
    return `<table width="100%" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF">
    <tr>
    <td style="font-size: 27px; color: #053682;">
    <a href="${url}" style="font-size: 20px; color: #053682; text-decoration: none;">${title}</a>
    </td>
    </tr>
    </table>`;
  },

  // Link #2

  6: function(title, url, intro, category) {
    return `<table width="100%" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF">
    <tr>
    <td style="font-size: 27px; color: #053682;">
    <a href="${url}" style="font-size: 20px; color: #053682; text-decoration: none;">${title}</a>
    </td>
    </tr>
    </table>`;
  },

  // Link #3

  7: function (title, url, intro, category) {
    return `<table width="100%" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF">
    <tr>
    <td style="font-size: 27px; color: #053682;">
    <a href="${url}" style="font-size: 20px; color: #053682; text-decoration: none;">${title}</a>
    </td>
    </tr>
    </table>`;
  },

  8: function (title, url, intro, category) {
    return `<table width="100%" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF">
    <tr>
    <td style="font-size: 27px; color: #053682;">
    <a href="${url}" style="font-size: 20px; color: #053682; text-decoration: none;">${title}</a>
    </td>
    </tr>
    </table>`;
  },

};

window.templates.placeholder = function() {
  return `
  <table width="${spacerWidth}" cellspacing="${spacerCellSpacing}" cellpadding="${spacerCellPadding}" border="${spacerBorder}">
  <tr>
  <td height="${spacerHeight}">&nbsp;</td>
  </tr>
  </table>
  <table width="100%" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF">
  <tr>
  <!-- Padding cell -->
  <td bgcolor="#101B1E" width="5">&nbsp;</td>

  <td style="font-size: 25px; color: #FFFFFF" bgcolor="#101B1E">
  Other News
  </td>
  </tr>
  </table>


  `;
};
