from bs4 import BeautifulSoup
import requests
import sys 
import csv

def make_request(url):
    response = requests.get(url)
    return response

def create_csv(filename, fields, fieldrows):
    with open(filename, 'w') as csvfile:        
        csvwriter = csv.writer(csvfile)
        csvwriter.writerow(fields)
        csvwriter.writerows(fieldrows)
    

position = sys.argv[1]
location = sys.argv[2]


job_links = []
job_extract_links = []
print("Extracting all the job links available on linkedIn.")
main_link = f'https://www.linkedin.com/jobs/search?keywords={position}&location={location}'
for i in range(50):
    next_page = f"{main_link}?pageNum={i}"
    response_main = make_request(next_page)
    soup_main = BeautifulSoup(response_main.content, 'html.parser')

    links = soup_main.find_all('a', {'class': 'base-card__full-link'})
    for link in links:
        job_links.append([link.get("href")])
        job_extract_links.append(link.get("href"))

# field names
fieldsLinks = ['Job Links']
filenamelinks = "linkedin_job_link_records.csv"
 
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
    job_title = soup.find('h1', {'class': 'top-card-layout__title'})
    job_description = soup.find('div', {'class': 'description__text'})
    job_org = soup.find('a', {'class': 'topcard__org-name-link'})
    job_loc = soup.find('span', {'class': 'topcard__flavor--bullet'})
    if len(soup.findAll('span', {'class': 'description__job-criteria-text'})) >= 4:
        job_seniority = soup.findAll('span', {'class': 'description__job-criteria-text'})[0]
        job_emp_type = soup.findAll('span', {'class': 'description__job-criteria-text'})[1]
        job_func = soup.findAll('span', {'class': 'description__job-criteria-text'})[2]
        job_ind = soup.findAll('span', {'class': 'description__job-criteria-text'})[3]
    job.extend([job_title.text.strip(),job_description.text.strip(), job_org.text.strip(), job_loc.text.strip(), job_seniority.text.strip() if job_seniority else "N/A", job_emp_type.text.strip() if job_emp_type else "N/A", job_func.text.strip() if job_func else "N/A", job_ind.text.strip() if job_ind else "N/A"])
    jobs.append(job)

# writing to Job Data csv file
fieldsMain = ['Job Name', 'Description', 'Organization', 'Location', 'Seniority Level', 'Employment Type', 'Job Function', 'Job Industry']
filenameData = "linkedin_job_main_records.csv"

# writing to Job Links csv file
create_csv(filenameData, fieldsMain, jobs)
print("Saved the extract Job Links Data to CSV File")

