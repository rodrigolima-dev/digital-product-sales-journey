import { HttpClient, HttpResponse } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { environment } from 'src/environments/environment';

@Injectable()
export class DadosPessoaisService {

  constructor(private http: HttpClient) { }

  salvar(dadosPessoais: any): Observable<HttpResponse<string>> {
    return this.http
      .post(
        `${environment.apiUrl}/mkt/leads`, 
        { fullName: dadosPessoais.nome, email: dadosPessoais.email },
        {
          headers: { "Content-Type": "application/json"},
          responseType: 'text',
          observe: 'response',
        }
      );
  }
}
