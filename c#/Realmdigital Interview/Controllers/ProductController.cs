using System;
using System.Collections.Generic;
using System.IO;
using System.Linq;
using System.Net;
using System.Web;
using System.Web.Http;
using Newtonsoft.Json;

namespace Retiremate_Integration_Services.Controllers
{
    public class ProductController
    {

        [Route("product")]
        public object GetProductById(string productId)
        {
            string response = "";

            using (var client = new WebClient())
            {
                client.Headers[HttpRequestHeader.ContentType] = "application/json";
                response = client.UploadString("http://192.168.0.241/eanlist?type=Web", "POST", "{ \"id\": \"" + productId + "\" }");
            }
            var reponseObject = JsonConvert.DeserializeObject<List<ApiResponseProduct>>(response);

            var result = new List<object>();
            for (int i = 0; i < reponseObject.Count; i++)
            {
                var prices = new List<object>();
                for (int j = 0; j < reponseObject[i].PriceRecords.Count; j++)
                {
                    if (reponseObject[i].PriceRecords[j].CurrencyCode == "ZAR")
                    {
                        prices.Add(new
                        {
                            Price = reponseObject[i].PriceRecords[j].SellingPrice,
                            Currency = reponseObject[i].PriceRecords[j].CurrencyCode
                        });
                    }
                }
                result.Add(new
                {
                    Id = reponseObject[i].BarCode,
                    Name = reponseObject[i].ItemName,
                    Prices = prices
                });
            }
            return result.Count > 0 ? result[0] : null;
        }

        [Route("product/search")]
        public List<object> GetProductsByName(string productName)
        {
            string response = "";

            using (var client = new WebClient())
            {
                client.Headers[HttpRequestHeader.ContentType] = "application/json";
                response = client.UploadString("http://192.168.0.241/eanlist?type=Web", "POST", "{ \"names\": \"" + productName + "\" }");
            }
            var reponseObject = JsonConvert.DeserializeObject<List<ApiResponseProduct>>(response);

            var result = new List<object>();
            for (int i = 0; i < reponseObject.Count; i++)
            {
                var prices = new List<object>();
                for (int j = 0; j < reponseObject[i].PriceRecords.Count; j++)
                {
                    if (reponseObject[i].PriceRecords[j].CurrencyCode == "ZAR")
                    {
                        prices.Add(new
                        {
                            Price = reponseObject[i].PriceRecords[j].SellingPrice,
                            Currency = reponseObject[i].PriceRecords[j].CurrencyCode
                        });
                    }
                }
                result.Add(new
                {
                    Id = reponseObject[i].BarCode,
                    Name = reponseObject[i].ItemName,
                    Prices = prices
                });
            }
            return result;
        }
    }



    class ApiResponseProduct
    {
        public string BarCode { get; set; }
        public string ItemName { get; set; }
        public List<ApiResponsePrice> PriceRecords { get; set; }
    }

    class ApiResponsePrice
    {
        public string SellingPrice { get; set; }
        public string CurrencyCode { get; set; }
    }
}