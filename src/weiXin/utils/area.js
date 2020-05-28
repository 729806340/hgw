var util = require('util.js');
var AreaJson = [];

function setAreaJson(Are) {
  AreaJson = Are;
}

function getData() {
  util.postUrl('area/get_list', {}, function(res) {
    if (res.data.code == 200) {
      console.log('1111111122222222', res.data.datas[17])
    }
  })
}
/**
 * 获取所有省份
 */
function getProvinces() {
  var provinces = [];
  for (var i = 0; i < AreaJson.length; i++) {
    provinces.push(AreaJson[i].area_name);
  }
  return provinces;
}

function getProvinces_2() {
  var provinces = [];
  for (var i = 0; i < AreaJson.length; i++) {
    provinces.push(AreaJson[i].area_name);
  }
  return provinces;
}

/**
 * 获取省对应的所有城市
 */
function getCitys(provinceIndex) {
  var citys = [{
    area_id: 0,
    area_name: "选择城市"
  }];
  if (provinceIndex == 0) {
    return citys;
  } else {
    provinceIndex = provinceIndex - 1;
  }
  for (var city in AreaJson[provinceIndex].city) {
    citys.push(AreaJson[provinceIndex].city[city]);
  }

  return citys;
}

function getCitys_2(provinceIndex) {
  var citys = [{
    area_id: 0,
    area_name: "选择街道"
  }];
  if (provinceIndex == 0) {
    return citys;
  } else {
    provinceIndex = provinceIndex - 1;
  }
  for (var city in AreaJson[provinceIndex].city) {
    citys.push(AreaJson[provinceIndex].city[city]);
  }

  return citys;
}

/**
 * 获取省市对应的所有地区
 */
function getAreas(provinceIndex, cityIndex) {
  var areas = [{
    area_id: 0,
    area_name: "选择区域"
  }];
  if (provinceIndex == 0 || cityIndex == 0) {
    return areas;
  } else {
    provinceIndex = provinceIndex - 1;
    cityIndex = cityIndex - 1;
  }
  var temp = AreaJson[provinceIndex].city[cityIndex].area;
  for (var i = 0; i < temp.length; i++) {
    areas.push(temp[i]);
  }
  return areas;
}
function getAreas_2(provinceIndex, cityIndex) {
  var areas = [{
    area_id: 0,
    area_name: "选择社区"
  }];
  if (provinceIndex == 0 || cityIndex == 0) {
    return areas;
  } else {
    provinceIndex = provinceIndex - 1;
    cityIndex = cityIndex - 1;
  }
  var temp = AreaJson[provinceIndex].city[cityIndex].area;
  for (var i = 0; i < temp.length; i++) {
    areas.push(temp[i]);
  }
  return areas;
}

module.exports = {
  getData: getData,
  getProvinces: getProvinces,
  getProvinces_2: getProvinces_2,
  getCitys: getCitys,
  getCitys_2: getCitys_2,
  getAreas: getAreas,
  getAreas_2: getAreas_2,
  setAreaJson: setAreaJson,
}