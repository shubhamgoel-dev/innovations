from bs4 import BeautifulSoup
import requests
import sys 
import csv


HEADERS = {
    "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36",
    "Accept-Encoding": "gzip, deflate, br",
    "Accept": "text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8",
    "Connection": "keep-alive",
    "Accept-Language": "en-US,en;q=0.9,lt;q=0.8,et;q=0.7,de;q=0.6",
}

def make_request(url):
    response = requests.get(url)
    return response

def create_csv(filename, fields, fieldrows):
    with open(filename, 'w') as csvfile:        
        csvwriter = csv.writer(csvfile)
        csvwriter.writerow(fields)
        csvwriter.writerows(fieldrows)
    

position = input("Enter the position you're looking for: ").replace(" ", "%20")
location = input("Enter the location: ").replace(" ", "%20")

job_links = []
job_extract_links = []
print("Extracting all the job links available on Indeed.com")
main_link = f'https://indeed.com/jobs?q={position}&l={location}&from=searchOnDesktopSerp'
#main_link = httpx.get("https://www.indeed.com/jobs?q={position}&l={location}", headers=HEADERS)

for i in range(0,800, 10):
    next_page = f"{main_link}&start={i}"
    print(next_page)
    response_main = make_request(next_page)
    soup_main = BeautifulSoup(response_main.content, 'html.parser')
    print(soup_main)
    links = soup_main.find_all('a', {'class': 'jcs-JobTitle'})
    print(links)
    for link in links:
        job_links.append([link.get("href")])
        job_extract_links.append(link.get("href"))

# field names
fieldsLinks = ['Job Links']
filenamelinks = "indeed_job_link_records.csv"
 
# writing to Job Links csv file
create_csv(filenamelinks, fieldsLinks, job_links)
print("Saved the Job Links to CSV File")


webpages = job_extract_links

jobs = []
print("Extracting Data of each link...")
for i in  range(len(webpages)):
    response = make_request(f"{webpages[i]}")
    soup = BeautifulSoup(response.content, 'html.parser')
    job = []
    job_title = soup.find('h1', {'class': 'jobsearch-JobInfoHeader-title'})
    job_description = soup.find('div', {'class': 'jobDescriptionText'})
    job_org = soup.find('div', {'data-testid': 'inlineHeader-companyName'})
    job_loc = soup.find('div', {'data-testid': 'inlineHeader-companyLocation'})
    job.extend([job_title.text.strip(),job_description.text.strip(), job_org.text.strip(), job_loc.text.strip()])
    jobs.append(job)

# writing to Job Data csv file
fieldsMain = ['Job Name', 'Description', 'Organization', 'Location']
filenameData = "indeed_job_main_records.csv"

# writing to Job Links csv file
create_csv(filenameData, fieldsMain, jobs)
print("Saved the extract Job Links Data to CSV File")

